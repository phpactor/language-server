<?php

namespace Phpactor\LanguageServer;

use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Extension\Core\CoreExtension;
use Phpactor\LanguageServer\Extension\Core\ExitServer;
use Phpactor\LanguageServer\Extension\Core\Initialize;
use Phpactor\LanguageServer\Extension\Core\Initialized;
use Phpactor\LanguageServer\Extension\Core\Session\Status;
use Phpactor\LanguageServer\Extension\Core\Shutdown;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidChange;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidClose;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidOpen;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidSave;
use Phpactor\LanguageServer\Extension\Core\TextDocument\WillSave;
use Phpactor\LanguageServer\Extension\Core\TextDocument\WillSaveWaitUntil;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Protocol\LanguageServerProtocol;
use Phpactor\LanguageServer\Core\Protocol\RecordingProtocol;
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

    /**
     * @var string
     */
    private $recordPath;

    /**
     * @var Extensions
     */
    private $extensions;

    private function __construct(Manager $sessionManager, ArgumentResolver $argumentResolver, LoggerInterface $logger)
    {
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
        $this->argumentResolver = $argumentResolver;
        $this->extensions = new Extensions([]);
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

    public function withConnection(Connection $connection)
    {
        $this->connection = function () use ($connection) {
            return $connection;
        };

        return $this;
    }

    public function addHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function withCoreExtension(): self
    {
        $this->extensions->add(
            new CoreExtension(
                $this->extensions,
                $this->sessionManager
            )
        );

        return $this;
    }

    public function recordTo(string $path)
    {
        $this->recordPath = $path;
    }

    public function build(): Server
    {
        $dispatcher = new MethodDispatcher($this->argumentResolver);

        if (null === $this->connection) {
            $this->stdIoServer();
        }

        $connectionFactory = $this->connection;

        $protocol = LanguageServerProtocol::create($this->logger);
        if ($this->recordPath) {
            $protocol = new RecordingProtocol(
                $protocol,
                $this->recordPath
            );
        }

        return new Server(
            $this->logger,
            $dispatcher,
            $connectionFactory(),
            $this->extensions,
            $protocol
        );
    }
}
