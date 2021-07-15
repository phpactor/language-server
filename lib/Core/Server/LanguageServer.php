<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Loop;
use Amp\Promise;
use Exception;
use Generator;
use Phpactor\LanguageServer\Core\Rpc\Exception\CouldNotCreateMessage;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
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
use function Amp\Promise\any;
use function Amp\call;

final class LanguageServer
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
     * @var StreamProvider
     */
    private $streamProvider;

    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var DispatcherFactory
     */
    private $dispatcherFactory;

    /**
     * @var Initializer
     */
    private $initializer;

    /**
     * @var ServerStats
     */
    private $stats;

    public function __construct(
        DispatcherFactory $dispatcherFactory,
        LoggerInterface $logger,
        StreamProvider $streamProvider,
        Initializer $initializer,
        ?ServerStats $stats = null
    ) {
        $this->logger = $logger;
        $this->streamProvider = $streamProvider;
        $this->dispatcherFactory = $dispatcherFactory;
        $this->initializer = $initializer;
        $this->stats = $stats ?: new ServerStats();
    }

    /**
     * Start the language server only. Event loop is not started.
     *
     * Return a promise which resolves when the language server stops
     *
     * @return Promise<void>
     */
    public function start(): Promise
    {
        return call(function () {
            yield from $this->listenForConnections();
        });
    }

    /**
     * Register signal handlers and run the language server in the event loop.
     */
    public function run(): void
    {
        Loop::onSignal(SIGINT, function (string $watcherId) {
            Loop::cancel($watcherId);
            yield $this->shutdown();
        });

        Loop::setErrorHandler(function (Throwable $error): void {
            if ($error instanceof ShutdownServer) {
                Loop::stop();
                return;
            }

            $this->logger->critical($error->getMessage());
            throw $error;
        });

        Loop::run(function () {
            yield $this->start();
        });
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

    /**
     * @return Promise<void>
     */
    public function shutdown(): Promise
    {
        return call(function () {
            $this->logger->info('Shutting down');

            $promises = [];
            foreach ($this->connections as $connection) {
                $promises[] = $connection->stream()->end();
            }

            yield any($promises);

            $this->streamProvider->close();
        });
    }

    private function listenForConnections(): Generator
    {
        if ($this->streamProvider instanceof SocketStreamProvider) {
            $this->logger->info(sprintf(
                'Listening on %s',
                $this->streamProvider->address()
            ));
        }

        // accept incoming connections (in the case of a TCP server this is
        // a connection, with a STDIO stream this just returns the stream
        // immediately)
        while ($connection = yield $this->streamProvider->accept()) {
            $this->stats->incConnectionCount();

            // create a reference to the connection so that we can later
            // terminate it if necessary
            $this->connections[$connection->id()] = $connection;

            \Amp\asyncCall(function () use ($connection) {
                yield $this->handle($connection);
                $this->stats->decConnectionCount();
            });
        }
    }

    /**
     * @return Promise<void>
     */
    private function handle(Connection $connection): Promise
    {
        return \Amp\call(function () use ($connection) {
            $transmitter = new ConnectionMessageTransmitter($connection, $this->logger);
            $reader = new LspMessageReader($connection->stream());
            $dispatcher = null;

            // wait for the next request
            while (null !== $request = yield $reader->wait()) {
                $this->logger->info('IN:', $request->body());
                $this->stats->incRequestCount();

                try {
                    $request = RequestMessageFactory::fromRequest($request);
                } catch (CouldNotCreateMessage $e) {
                    $transmitter->transmit(new ResponseMessage(
                        $request->body()['id'] ?? 0,
                        [],
                    ));
                    continue;
                }

                // initialize the dispatcher with the initialize parameters
                // (for example to allow a container to boot with the client
                // capabilities)
                if (null === $dispatcher) {
                    $dispatcher = $this->dispatcherFactory->create(
                        $transmitter,
                        $this->initializer->provideInitializeParams($request)
                    );
                }

                $this->dispatchRequest($transmitter, $dispatcher, $connection, $request);
            };
        });
    }

    private function dispatchRequest(MessageTransmitter $transmitter, Dispatcher $dispatcher, Connection $connection, Message $request): void
    {
        \Amp\asyncCall(function () use ($transmitter, $request, $dispatcher, $connection) {
            try {
                $response = yield $dispatcher->dispatch($request);
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
    }
}
