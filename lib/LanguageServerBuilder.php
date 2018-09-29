<?php

namespace Phpactor\LanguageServer;

use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\ExitServer;
use Phpactor\LanguageServer\Core\Handler\Initialize;
use Phpactor\LanguageServer\Core\Handler\Server\Status;
use Phpactor\LanguageServer\Core\Handler\Shutdown;
use Phpactor\LanguageServer\Core\Handler\TextDocument\DidChange;
use Phpactor\LanguageServer\Core\Handler\TextDocument\DidOpen;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Session\Manager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LanguageServerBuilder
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Closure
     */
    private $connection;

    /**
     * @var Handler[]
     */
    private $handlers;

    /**
     * @var Manager
     */
    private $sessionManager;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    private function __construct(Manager $sessionManager, ArgumentResolver $argumentResolver, LoggerInterface $logger)
    {
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
        $this->argumentResolver = $argumentResolver;
    }

    public static function create(LoggerInterface $logger = null, Manager $sessionManager = null): self
    {
        return new self(
            $sessionManager ?: new Manager(),
            new DTLArgumentResolver(),
            $logger ?: new NullLogger()
        );
    }

    public function tcpServer(string $address = '127.0.0.1:8888'): self
    {
        $this->connection = function () use ($address) {
            return new TcpServerConnection($this->logger, $address);
        };

        return $this;
    }

    public function stdIoServer(): self
    {
        $this->connection = function () {
            return new StreamConnection($this->logger);
        };

        return $this;
    }

    public function addHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function coreHandlers(): self
    {
        $this->handlers[] = new Initialize($this->sessionManager);
        $this->handlers[] = new ExitServer();
        $this->handlers[] = new Shutdown();

        $this->handlers[] = new DidOpen($this->sessionManager);
        $this->handlers[] = new DidChange($this->sessionManager);
        $this->handlers[] = new Status($this->sessionManager);

        return $this;
    }

    public function build(): Server
    {
        $dispatcher = new MethodDispatcher($this->argumentResolver, new Handlers($this->handlers));

        if (null === $this->connection) {
            $this->stdIoServer();
        }

        $connectionFactory = $this->connection;

        return new Server($this->logger, $dispatcher, $connectionFactory());
    }
}
