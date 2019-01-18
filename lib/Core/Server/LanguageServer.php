<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use DateTimeImmutable;
use Generator;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\StreamProvider;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use RuntimeException;

final class LanguageServer implements StatProvider
{
    private const WRITE_CHUNK_SIZE = 256;

    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LanguageServerProtocolWriter
     */
    private $writer;

    /**
     * @var StreamProvider
     */
    private $streamProvider;

    /**
     * @var bool
     */
    private $enableEventLoop;

    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var Handlers
     */
    private $systemHandlers;

    /**
     * @var DateTimeImmutable
     */
    private $created;

    /**
     * @var int
     */
    private $requestCount = 0;

    /**
     * @var HandlerLoader
     */
    private $handlerLoader;

    public function __construct(
        Dispatcher $dispatcher,
        Handlers $systemHandlers,
        HandlerLoader $handlerLoader,
        LoggerInterface $logger,
        StreamProvider $streamProvider,
        bool $enableEventLoop = true
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->handlerLoader = $handlerLoader;

        $this->streamProvider = $streamProvider;
        $this->enableEventLoop = $enableEventLoop;

        $this->writer = new LanguageServerProtocolWriter();
        $this->created = new DateTimeImmutable();

        $this->systemHandlers = $this->addSystemHandlers($systemHandlers);
    }

    /**
     * Start the server in an event loop
     */
    public function start(): void
    {
        try {
            $this->doStart();
        } catch (ShutdownServer $e) {
        }
    }

    /**
     * Returns the address of the server if the server is running
     * as a socket server, otherwise throws an exception.
     *
     * @throws RuntimeException
     */
    public function address(): ?string
    {
        if (!$this->streamProvider instanceof SocketStreamProvider) {
            throw new RuntimeException(sprintf(
                'Cannot get address on non-socket stream provider, using "%s"',
                get_class($this->streamProvider)
            ));
        }

        return $this->streamProvider->address();
    }

    public function stats(): ServerStats
    {
        return new ServerStats(
            $this->created->diff(new DateTimeImmutable()),
            count($this->connections),
            $this->requestCount
        );
    }

    /**
     * {@inheritDoc}
     */
    private function doStart()
    {
        $this->logger->info(sprintf('Process ID: %s', getmypid()));
        
        Loop::onSignal(SIGINT, function (string $watcherId) {
            Loop::cancel($watcherId);
            yield $this->shutdown();
        });
        
        if (false === $this->enableEventLoop) {
            $this->listenForConnections();
            return;
        }
        
        Loop::run(function () {
            $this->listenForConnections();
        });
    }

    private function listenForConnections(): void
    {
        if ($this->streamProvider instanceof SocketStreamProvider) {
            $this->logger->info(sprintf(
                'Listening on %s',
                $this->streamProvider->address()
            ));
        }

        \Amp\asyncCall(function () {

            // accept incoming connections (in the case of a TCP server this is
            // a connection, with a STDIO stream this just returns the stream
            // immediately.)
            while ($connection = yield $this->streamProvider->accept()) {

                // create a reference to the connection so that we can later
                // terminate it if necessary
                $this->connections[$connection->id()] = $connection;

                // handle the request as a co-routine. If the handler throws an
                // ExitSession and this is a TCP server then end then continue,
                // otherewise this is a STDIO session so re-throw a Shutdown
                // exception and exit the process.
                \Amp\asyncCall(function () use ($connection) {
                    try {
                        yield from $this->handle($connection);
                    } catch (ExitSession $exception) {
                        $connection->stream()->end();

                        if ($this->streamProvider instanceof ResourceStreamProvider) {
                            throw new ShutdownServer(
                                'Exit called on STDIO connection, exiting the server'
                            );
                        }
                    }
                });
            }
        });
    }

    private function handle(Connection $connection): Generator
    {
        $container = new ApplicationContainer(
            $this->dispatcher,
            $this->systemHandlers,
            $this->handlerLoader
        );

        $parser = new LanguageServerProtocolParser(function (
            Request $request
        ) use (
            $container,
            $connection
        ) {
            $this->logger->info('REQUEST', $request->body());
            $this->requestCount++;

            $responses = $container->dispatch(
                RequestMessageFactory::fromRequest($request)
            );

            foreach ($responses as $response) {
                $this->logger->info('RESPONSE', (array) $response);

                $responseBody = $this->writer->write($response);

                foreach (str_split($responseBody, self::WRITE_CHUNK_SIZE) as $chunk) {
                    $connection->stream()->write($chunk);
                }
            }
        });

        while (null !== ($chunk = yield $connection->stream()->read())) {
            $parser->feed($chunk);
        }
    }

    private function shutdown(): Promise
    {
        return new Coroutine($this->doShutdown());
    }

    private function doShutdown(): Generator
    {
        $this->logger->info('Shutting down');

        $promises = [];
        foreach ($this->connections as $connection) {
            $promises[] = $connection->stream()->end();
        }

        \Amp\Promise\wait(\Amp\Promise\any($promises));

        $this->streamProvider->close();

        throw new ShutdownServer('shutdown server invoked');
    }

    private function addSystemHandlers(Handlers $handlers): Handlers
    {
        $handlers->add(new SystemHandler($this));
        $handlers->add(new ExitHandler());

        return $handlers;
    }
}
