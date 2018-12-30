<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Deferred;
use Amp\Promise;
use Amp\Socket\Server;
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

    public function provide(): Promise
    {
        $promise = $this->server->accept();
        $this->logger->info(sprintf('Listening on %s', $this->server->getAddress()));

        $deferrer = new Deferred();
        $promise->onResolve(function ($reason, $value) use ($deferrer) {
            $deferrer->resolve(new SocketDuplexStream($value));
        });

        return $deferrer->promise();
    }

    public function address(): ?string
    {
        return $this->server->getAddress();
    }
}
