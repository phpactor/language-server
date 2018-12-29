<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\ServerSocket;
use Generator;
use Phpactor\LanguageServer\Core\Protocol\LspReader;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Transport\Request;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server as ReactSocketServer;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

class TcpServer implements Server
{
    /**
     * @var LoopInterface
     */
    private $eventLoop;

    /**
     * @var string
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

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        string $address
    )
    {
        $this->logger = $logger;
        $this->address = $address;
        $this->dispatcher = $dispatcher;
        $this->writer = new LanguageServerProtocolWriter();
    }

    public function start(): void
    {
        \Amp\asyncCall(function () {
            $server = \Amp\Socket\listen($this->address);
            $this->logger->info(sprintf('I am listening on "%s"', $server->getAddress()));
            $handler = $this->createHandler();

            while ($socket = yield $server->accept()) {
                \Amp\asyncCall($handler, $socket);
            }
        });
    }

    private function createHandler()
    {
        return function (ServerSocket $socket) {

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
        $this->logger->info('Dispatching request', $request->body());

        $responses = $this->dispatcher->dispatch(RequestMessageFactory::fromRequest($request));

        foreach ($responses as $response) {
            $socket->write($this->writer->write($response));
        }
    }
}
