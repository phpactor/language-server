<?php

namespace Phpactor\LanguageServer;

use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Server\TcpServer;
use Phpactor\LanguageServer\Extension\Core\CoreExtension;
use Phpactor\LanguageServer\Core\Protocol\LanguageServerProtocol;
use Phpactor\LanguageServer\Core\Protocol\RecordingProtocol;
use Phpactor\LanguageServer\Core\Server\Server;
use Phpactor\LanguageServer\Core\Session\Manager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use SessionHandler;

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

    public function build(string $address = '127.0.0.1:8888'): Server
    {
        $dispatcher = new ErrorCatchingDispatcher(
            new MethodDispatcher(
                new DTLArgumentResolver(),
                (new CoreExtension(
                    new Manager()
                ))->handlers()
            ),
            $this->logger
        );

        return new TcpServer($dispatcher, $this->logger, $address);
    }
}
