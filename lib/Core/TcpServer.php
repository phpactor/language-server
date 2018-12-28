<?php

namespace Phpactor\LanguageServer\Core;

use Amp\ByteStream\StreamException;
use Amp\Loop;
use Amp\Socket\ServerSocket;
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
        $parser = new LanguageServerProtocolParser(function (Request $request) {
            $this->logger->info('Dispatching', $request->body());
            $requestMessage = RequestMessageFactory::fromRequest($request);
            $this->dispatcher->dispatch(new Handlers(), $requestMessage);
        });

        Loop::run(function () use ($parser) {
            $server = \Amp\Socket\listen($this->address);

            $handler = function (ServerSocket $socket) use ($parser) {
                while (null !== $chunk = yield $socket->read()) {
                    $parser->feed($chunk);
                    try {
                        yield $socket->write($chunk);
                    } catch (StreamException $exception) {
                        $this->logger->error($exception->getMessage());
                        yield $socket->end();
                    }
                }
            };

            while ($socket = yield $server->accept()) {
                    \Amp\asyncCall($handler, $socket);
            }


        });
    }

    public function address(): string
    {
        return $this->address;
    }
}
