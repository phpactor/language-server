<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\Server as SocketServer;
use Generator;
use Phpactor\LanguageServer\Core\Server\Exception\ServerControlException;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
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
    const WRITE_CHUNK_SIZE = 256;

    /**
     * @var LoopInterface
     */
    private $eventLoop;

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
     * @var SocketServer
     */
    private $server;

    /**
     * @var StreamProvider
     */
    private $streamProvider;

    /**
     * @var bool
     */
    private $enableEventLoop;

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
            throw new ShutdownServer('SIGINT received');
        });

        try {
            if ($this->enableEventLoop) {
                Loop::run(function () {
                    $this->waitForConnections();
                });
                return;
            }
        } catch (ShutdownServer $shutdown) {
            $this->logger->info(sprintf('Shutdown Exception Received: %s', $shutdown->getMessage()));
            return;
        }

        $this->waitForConnections();
    }

    protected function waitForConnections(): void
    {
        \Amp\asyncCall(function () {
            while ($stream = yield $this->streamProvider->provide()) {
                \Amp\asyncCall(function () use ($stream) {
                    return $this->handle($stream);
                });
            }
        });
    }

    private function handle(DuplexStream $stream): Generator
    {
        $parser = (new LanguageServerProtocolParser())->__invoke();

        while (null !== ($chunk = yield $stream->read())) {
            while ($request = $parser->send($chunk)) {
                try {
                    $this->dispatch($request, $stream);
                } catch (ServerControlException $exception) {
                    $this->logger->info($exception->getMessage());
                    \Amp\Promise\wait($stream->end());

                    throw $exception;
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
}
