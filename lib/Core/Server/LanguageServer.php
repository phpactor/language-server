<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\Server as SocketServer;
use Amp\Socket\ServerSocket;
use Generator;
use Phpactor\LanguageServer\Core\Server\Exception\ServerControlException;
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

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        StreamProvider $streamProvider
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;

        $this->writer = new LanguageServerProtocolWriter();
        $this->streamProvider = $streamProvider;
    }

    public function address(): string
    {
        if (!$this->streamProvider instanceof SocketStreamProvider) {
            throw new RuntimeException(sprintf(
                'Cannot get address on non-socket stream provider, using "%s"',
                get_class($this->streamProvider)
            ));
        }

        return $this->streamProvider->address();
    }

    public function start(): void
    {
        Loop::run(function () {
            $this->startNoLoop();
        });
    }

    public function startNoLoop(): void
    {
        \Amp\asyncCall(function () {
            while ($stream = yield $this->streamProvider->provide()) {
                $value = yield from $this->handle($stream);
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

                } catch (StreamException $exception) {
                    $this->logger->error($exception->getMessage());

                    yield $stream->end();
                } catch (ServerControlException $exception) {
                    $this->logger->info($exception->getMessage());

                    yield $stream->end();
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
            $socket->write($this->writer->write($response));
        }
    }
}
