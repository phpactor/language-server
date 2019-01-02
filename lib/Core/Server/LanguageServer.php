<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server as SocketServer;
use Amp\Success;
use Generator;
use Phpactor\LanguageServer\Core\Server\Exception\ServerControlException;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\StreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use RuntimeException;

class LanguageServer
{
    private const WRITE_CHUNK_SIZE = 256;
    private const STATE_RUNNING = 'running';
    private const STATE_STARTING = 'starting';
    private const STATE_SHUTTING_DOWN = 'shutting_down';

    private $state = self::STATE_STARTING;

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

    private $acceptWatcherIds = [];

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
        $this->logger->info(sprintf('Process ID: %s', getmypid()));
        Loop::onSignal(SIGINT, function (string $watcherId) {
            Loop::cancel($watcherId);
            yield $this->shutdown();
        });

        if ($this->enableEventLoop) {
            Loop::run(function () {
                $this->waitForConnections();

                if ($this->isShuttingDown()) {
                    Loop::stop();
                }

            });

            return;
        }

        $this->waitForConnections();
    }

    protected function waitForConnections(): void
    {
        $this->state = self::STATE_RUNNING;

        if ($this->streamProvider instanceof SocketStreamProvider) {
            $this->logger->info(sprintf(
                'Listening on %s',
                $this->streamProvider->address()
            ));
        }

        \Amp\asyncCall(function () {
            /** @var Connection $connection */
            while (
                $this->isRunning() &&
                $connection = yield $this->streamProvider->provide()
            ) {
                $this->connections[$connection->id()] = $connection;

                \Amp\asyncCall(function () use ($connection) {
                    return $this->handle($connection->stream());
                });
            }
        });
    }

    private function handle(DuplexStream $stream): Generator
    {
        $parser = (new LanguageServerProtocolParser())->__invoke();

        while (
            $this->isRunning() && 
            null !== ($chunk = yield $stream->read())
        ) {
            while ($this->isRunning() && $request = $parser->send($chunk)) {
                try {
                    $this->dispatch($request, $stream);
                } catch (ServerControlException $exception) {
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

    private function isRunning(): bool
    {
        return $this->state === self::STATE_RUNNING;
    }

    private function shutdown(): Promise
    {
        $this->logger->info('Shutting down');
        $this->state = self::STATE_SHUTTING_DOWN;

        foreach ($this->acceptWatcherIds as $watcherId) {
            Loop::cancel($watcherId);
        }

        $proimises = [];
        foreach ($this->connections as $connection) {
            $promises[] = $connection->stream()->end();
        }

        return \Amp\Promise\any($proimises);
    }

    private function isShuttingDown(): bool
    {
        return $this->state === self::STATE_SHUTTING_DOWN;
    }
}
