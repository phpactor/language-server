<?php

namespace Phpactor\LanguageServer;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\EventSubscriber;
use Phpactor\LanguageServer\Core\Handler\ExitHandler;
use Phpactor\LanguageServer\Core\Handler\InitializeHandler;
use Phpactor\LanguageServer\Core\Handler\SystemHandler;
use Phpactor\LanguageServer\Core\Handler\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LanguageServerBuilder
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Handler[]
     */
    private $handlers = [];

    /**
     * @var EventEmitter
     */
    private $eventEmitter;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var bool
     */
    private $defaultHandlers = true;

    /**
     * @var bool
     */
    private $catchExceptions = true;

    /**
     * @var string|null
     */
    private $tcpAddress = null;

    /**
     * @var bool
     */
    private $eventLoop = true;

    private function __construct(
        LoggerInterface $logger,
        EventEmitter $eventEmitter,
        SessionManager $sessionManager
    ) {
        $this->logger = $logger;
        $this->eventEmitter = $eventEmitter;
        $this->sessionManager = $sessionManager;
    }

    public static function create(
        LoggerInterface $logger = null,
        SessionManager $sessionManager = null,
        EventEmitter $eventEmitter = null
    ): self {
        return new self(
            $logger ?: new NullLogger(),
            $eventEmitter ?: new EvenementEmitter(),
            $sessionManager ?: new SessionManager()
        );
    }

    public function useDefaultHandlers(bool $useDefaultHandlers = true): self
    {
        $this->defaultHandlers = $useDefaultHandlers;

        return $this;
    }

    public function catchExceptions(bool $enabled = true): self
    {
        $this->catchExceptions = $enabled;

        return $this;
    }

    public function eventLoop(bool $enabled = true): self
    {
        $this->eventLoop = $enabled;

        return $this;
    }

    public function addHandler(Handler $handler): self
    {
        if ($handler instanceof EventSubscriber) {
            foreach ($handler->events() as $eventName => $method) {
                $this->eventEmitter->on($eventName, Closure::fromCallable([ $handler, $method ]));
            }
        }

        $this->handlers[] = $handler;

        return $this;
    }

    public function tcpServer(?string $address = '0.0.0.0:0'): self
    {
        $this->tcpAddress = $address;

        return $this;
    }

    /**
     * Build the language server.
     * The returned language server can then be started
     * by calling `start()`.
     */
    public function build(): LanguageServer
    {
        $dispatcher = $this->buildDispatcher();

        if ($this->tcpAddress) {
            $provider = new SocketStreamProvider(
                \Amp\Socket\listen($this->tcpAddress),
                $this->logger
            );
        } else {
            $provider = new ResourceStreamProvider(
                new ResourceDuplexStream(
                    new ResourceInputStream(STDIN),
                    new ResourceOutputStream(STDOUT)
                ),
                $this->logger
            );
        }

        return new LanguageServer(
            $dispatcher,
            $this->logger,
            $provider,
            $this->eventLoop
        );
    }

    /**
     * Return the RPC dispatcher used by the server.
     * Useful for testing.
     */
    public function buildDispatcher(): Dispatcher
    {
        if ($this->defaultHandlers) {
            $this->addDefaultHandlers();
        }

        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver(),
            new Handlers($this->handlers)
        );

        if ($this->catchExceptions) {
            $dispatcher = new ErrorCatchingDispatcher(
                $dispatcher,
                $this->logger
            );
        }

        return $dispatcher;
    }

    private function addDefaultHandlers(): void
    {
        $this->addHandler(new InitializeHandler(
            $this->eventEmitter,
            $this->sessionManager
        ));
        $this->addHandler(
            new TextDocumentHandler($this->eventEmitter, $this->sessionManager)
        );
        $this->addHandler(new ExitHandler());
        $this->addHandler(new SystemHandler($this->sessionManager));
    }
}
