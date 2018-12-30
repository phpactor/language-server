<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\Server as SocketServer;
use Amp\Socket\ServerSocket;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

class TcpServer implements Server
{
    /**
     * @var LoopInterface
     */
    private $eventLoop;

    /**
     * @var string|null
     */
    private $address;

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

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        string $address
    ) {
        $this->logger = $logger;
        $this->address = $address;
        $this->dispatcher = $dispatcher;
        $this->writer = new LanguageServerProtocolWriter();
        $this->server = \Amp\Socket\listen($this->address);
    }

    public function address(): ?string
    {
        return $this->server->getAddress();
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
            $this->logger->info(sprintf('I am listening on "%s"', $this->server->getAddress()));
            $handler = $this->createHandler();

            while ($socket = yield $this->server->accept()) {
                $this->logger->info(sprintf('Accepted connection on %s', $this->server->getAddress()));
                \Amp\asyncCall($handler, $socket);
            }
        });
    }

    private function createHandler()
    {
        return function (InputStream $socket) {
            $parser = (new LanguageServerProtocolParser())->__invoke();

            while (null !== $chunk = yield $socket->read()) {
                while ($request = $parser->send($chunk)) {
                    try {
                        $this->dispatch($request, $socket);
                    } catch (StreamException $exception) {
                        $this->logger->error($exception->getMessage());

                        yield $socket->end();
                    }
                    $chunk = null;
                }
            }
        };
    }

    private function dispatch(Request $request, ServerSocket $socket)
    {
        $this->logger->info('Request', $request->body());

        $responses = $this->dispatcher->dispatch(RequestMessageFactory::fromRequest($request));

        foreach ($responses as $response) {
            $this->logger->info('Response', (array) $response);
            $socket->write($this->writer->write($response));
        }
    }
}
