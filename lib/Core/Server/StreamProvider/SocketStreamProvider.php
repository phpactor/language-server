<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Deferred;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Socket\Socket;
use Phpactor\LanguageServer\Core\Server\Stream\SocketDuplexStream;
use Psr\Log\LoggerInterface;

class SocketStreamProvider implements StreamProvider
{
    /**
     * @var Server
     */
    private $server;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Server $server, LoggerInterface $logger)
    {
        $this->server = $server;
        $this->logger = $logger;
    }

    public function accept(): Promise
    {
        $promise = $this->server->accept();

        $deferred = new Deferred();
        $promise->onResolve(function ($reason, Socket $socket) use ($deferred) {
            $this->logger->info(sprintf('Accepted connection from "%s"', $socket->getRemoteAddress()));
            $deferred->resolve(new Connection($socket->getRemoteAddress(), new SocketDuplexStream($socket)));
        });

        return $deferred->promise();
    }

    public function address(): ?string
    {
        return $this->server->getAddress();
    }

    public function close(): void
    {
        $this->server->close();
    }
}
