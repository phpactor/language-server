<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Protocol\LspReader;
use Phpactor\LanguageServer\Core\Transport\Request;
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

    public function __construct(
        LoopInterface $eventLoop,
        LoggerInterface $logger,
        string $address
    )
    {
        $this->parser = new LanguageServerProtocolParser();
        $this->logger = $logger;

        $server = new ReactSocketServer($address, $eventLoop);
        $server->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', [ $this->parser, 'feed' ]);
        });

        $this->parser->on(LanguageServerProtocolParser::EVENT_REQUEST_READY, function (Request $request) {
        });
        $this->address = $server->getAddress();
        $this->eventLoop = $eventLoop;
    }


    public function start(): void
    {
        $this->eventLoop->run();
    }

    public function address(): string
    {
        return $this->address;
    }
}
