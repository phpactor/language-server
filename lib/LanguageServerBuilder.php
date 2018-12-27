<?php

namespace Phpactor\LanguageServer;

use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\TcpServer;
use Phpactor\LanguageServer\Extension\Core\CoreExtension;
use Phpactor\LanguageServer\Core\Protocol\LanguageServerProtocol;
use Phpactor\LanguageServer\Core\Protocol\RecordingProtocol;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Session\Manager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;

class LanguageServerBuilder
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function create(LoggerInterface $logger = null): self
    {
        return new self(
            $logger ?: new NullLogger()
        );
    }

    public function tcpServer(string $address = '127.0.0.1:8888'): self
    {
        $this->server = new TcpServer($this->logger, $address);

        return $this;
    }

    public function stdIoServer(): self
    {
        $this->connection = function () {
            return new StreamConnection($this->logger);
        };

        return $this;
    }

    public function addExtension(Extension $extension)
    {
        $this->extensions->add($extension);

        return $this;
    }

    public function build(): Server
    {
        return $this->server;
    }
}
