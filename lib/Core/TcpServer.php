<?php

namespace Phpactor\LanguageServer\Core;

use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\ServerSocket;
use Generator;
use Phpactor\LanguageServer\Core\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Protocol\LspReader;
use Phpactor\LanguageServer\Core\Transport\Request;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\RequestMessageFactory;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server as ReactSocketServer;

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

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger,
        string $address
    )
    {
        $this->logger = $logger;
        $this->address = $address;
        $this->dispatcher = $dispatcher;
    }

    public function start(): void
    {
        \Amp\asyncCall(function () {
            $server = \Amp\Socket\listen($this->address);
            $handler = $this->createHandler();

            while ($socket = yield $server->accept()) {
                \Amp\asyncCall($handler, $socket);
            }
        });
    }

    private function createHandler()
    {
       return function (ServerSocket $socket) {
        
            $parser = new LanguageServerProtocolParser();

            while (null !== $chunk = yield $socket->read()) {

                foreach ($parser->feed($chunk) as $request) {

                    if (null === $request) {
                        continue 2;
                    }

                    $this->dispatch($request, $socket);

                    try {
                        yield $socket->write($chunk);
                    } catch (StreamException $exception) {
                        $this->logger->error($exception->getMessage());
                        yield $socket->end();
                    }
                }
            }
       };
    }

    private function dispatch(Request $request, ServerSocket $socket)
    {
        $this->logger->info('Dispatching request', $request->body());

        $responses = $this->dispatcher->dispatch(RequestMessageFactory::fromRequest($request));

        foreach ($responses as $response) {
            $socket->write(json_encode($response));
        }
    }
}
