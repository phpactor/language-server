<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Deferred;
use Amp\Promise;
use Amp\Socket\Server;
use Phpactor\LanguageServer\Core\Server\Stream\SocketDuplexStream;

class SocketStreamProvider implements StreamProvider
{
    /**
     * @var Server
     */
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function provide(): Promise
    {
        $promise = $this->server->accept();

        $deferrer = new Deferred();
        $promise->onResolve(function ($reason, $value) use ($deferrer) {
            $deferrer->resolve(new SocketDuplexStream($value));
        });

        return $deferrer->promise();
    }

    public function address(): string
    {
        return $this->server->getAddress();
    }
}
