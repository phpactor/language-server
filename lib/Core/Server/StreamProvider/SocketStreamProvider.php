<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Deferred;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Socket\Socket;
use Phpactor\LanguageServer\Core\Server\Stream\SocketDuplexStream;
use Psr\Log\LoggerInterface;
use Throwable;

final class SocketStreamProvider implements StreamProvider
{
    public function __construct(private Server $server, private LoggerInterface $logger)
    {
    }

    public function accept(): Promise
    {
        $promise = $this->server->accept();

        $deferred = new Deferred();
        $promise->onResolve(function (?Throwable $reason, mixed $socket) use ($deferred): void {
            if (!$socket instanceof Socket) {
                return;
            }

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
