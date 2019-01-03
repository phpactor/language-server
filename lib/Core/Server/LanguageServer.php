<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Generator;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\StreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use RuntimeException;

class LanguageServer
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

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        StreamProvider $streamProvider,
        bool $enableEventLoop = true
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;

        $this->writer = new LanguageServerProtocolWriter();
        $this->streamProvider = $streamProvider;
        $this->enableEventLoop = $enableEventLoop;
    }

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

    private function doStart()
    {
        $this->logger->info(sprintf('Process ID: %s', getmypid()));
        
        Loop::onSignal(SIGINT, function (string $watcherId) {
            Loop::cancel($watcherId);
            yield $this->shutdown();
        });
        
        if (!$this->enableEventLoop) {
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
            /** @var Connection $connection */
            while (
                $connection = yield $this->streamProvider->accept()
            ) {
                $this->connections[] = $connection;

                \Amp\asyncCall(function () use ($connection) {
                    try {
                        yield from $this->handle($connection->stream());
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

    private function handle(DuplexStream $stream): Generator
    {
        $parser = (new LanguageServerProtocolParser())->__invoke();

        while (
            null !== ($chunk = yield $stream->read())
        ) {
            while ($request = $parser->send($chunk)) {
                try {
                    $this->dispatch($request, $stream);
                } catch (ShutdownServer $exception) {
                    $this->logger->info($exception->getMessage());
                    yield $this->shutdown();
                }

                $chunk = null;
            }
        }
    }

    private function dispatch(Request $request, DuplexStream $socket)
    {
        $this->logger->info('Request', $request->body());

        $responses = $this->dispatcher->dispatch(RequestMessageFactory::fromRequest($request));

        foreach ($responses as $response) {
            $this->logger->info('Response', (array) $response);
            $responseBody = $this->writer->write($response);

            foreach (str_split($responseBody, self::WRITE_CHUNK_SIZE) as $chunk) {
                $socket->write($chunk);
            }
        }
    }

    private function shutdown(): Promise
    {
        return new Coroutine($this->doShutdown());
    }

    private function doShutdown(): Generator
    {
        $this->logger->info('Shutting down');

        $proimises = [];
        foreach ($this->connections as $connection) {
            $promises[] = $connection->stream()->end();
        }

        \Amp\Promise\wait(\Amp\Promise\any($proimises));

        $this->streamProvider->close();

        throw new ShutdownServer('shutdown server invoked');
    }
}
