<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use DateTimeImmutable;
use Exception;
use Generator;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Exception\CouldNotCreateMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\StreamProvider;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use RuntimeException;
use Throwable;

final class LanguageServer implements StatProvider
{
    /**
     * @var RequestReader
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
     * @var HandlerLoader
     */
    private $handlerLoader;

    /**
     * @var int
     */
    private $requestCount = 0;

    /**
     * @var ResponseWatcher
     */
    private $responseWatcher;

    public function __construct(
        Dispatcher $dispatcher,
        Handlers $systemHandlers,
        HandlerLoader $handlerLoader,
        LoggerInterface $logger,
        StreamProvider $streamProvider,
        ResponseWatcher $responseWatcher,
        bool $enableEventLoop = true
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->handlerLoader = $handlerLoader;

        $this->streamProvider = $streamProvider;
        $this->enableEventLoop = $enableEventLoop;

        $this->created = new DateTimeImmutable();

        $this->systemHandlers = $this->addSystemHandlers($systemHandlers);
        $this->responseWatcher = $responseWatcher;
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
    private function doStart(): void
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
        
        Loop::setErrorHandler(function (Throwable $error) {
            $this->logger->critical($error->getMessage());
            throw $error;
        });

        Loop::run(function () {
            $this->listenForConnections();
        });
    }

    private function listenForConnections(): void
    {
        \Amp\asyncCall(function () {
            if ($this->streamProvider instanceof SocketStreamProvider) {
                $this->logger->info(sprintf(
                    'Listening on %s',
                    $this->streamProvider->address()
                ));
            }

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
                    yield $this->handle($connection);
                });
            }
        });
    }

    /**
     * @return Promise<void>
     */
    private function handle(Connection $connection): Promise
    {
        return \Amp\call(function () use ($connection) {
            $transmitter = new ConnectionMessageTransmitter($connection, $this->logger);
            $serverClient = new ServerClient($transmitter, $this->responseWatcher);
            $serviceManager = new ServiceManager($transmitter, $this->logger, new DTLArgumentResolver());
            $container = new ApplicationContainer(
                $this->dispatcher,
                $this->systemHandlers,
                $this->handlerLoader,
                new SessionServices($transmitter, $serviceManager, $serverClient)
            );

            $reader = new LspMessageReader($connection->stream());

            while (null !== $request = yield $reader->wait()) {
                $this->logger->info('IN :', $request->body());
                $this->requestCount++;

                try {
                    $request = RequestMessageFactory::fromRequest($request);
                } catch (CouldNotCreateMessage $e) {
                    $transmitter->transmit(new ResponseMessage(
                        $request->body()['id'] ?? 0,
                        [],
                    ));
                    continue;
                }

                \Amp\asyncCall(function () use ($serviceManager, $request, $container, $transmitter, $connection, $serverClient) {
                    try {
                        $response = yield $container->dispatch($request, [
                            '_transmitter' => $transmitter,
                            '_serverClient' => $serverClient,
                            '_serviceManager' => $serviceManager,
                        ]);
                    } catch (ExitSession $e) {
                        $connection->stream()->end();

                        if ($this->streamProvider instanceof ResourceStreamProvider) {
                            throw new ShutdownServer(
                                'Exit called on STDIO connection, exiting the server'
                            );
                        }
                        return;
                    }

                    if (null === $response) {
                        return;
                    }

                    $transmitter->transmit($response);
                });
            };
        });
    }

    /**
     * @return Promise<void>
     */
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
