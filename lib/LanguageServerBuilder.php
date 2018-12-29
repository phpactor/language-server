<?php

namespace Phpactor\LanguageServer;

use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\EventSubscriber;
use Phpactor\LanguageServer\Core\Handler\ExitHandler;
use Phpactor\LanguageServer\Core\Handler\InitializeHandler;
use Phpactor\LanguageServer\Core\Handler\SessionHandler;
use Phpactor\LanguageServer\Core\Handler\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Server\TcpServer;
use Phpactor\LanguageServer\Core\Server\Server;
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
     * @var string
     */
    private $tcpAddress = '0.0.0.0:0';

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

    public function tcpServer(string $address = '0.0.0.0:0'): self
    {
        $this->tcpAddress = $address;

        return $this;
    }

    public function build(): Server
    {
        if ($this->defaultHandlers) {
            $this->addDefaultHandlers();
        }

        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver(),
            new Handlers($this->handlers)
        );

        if ($this->catchExceptions) {
            $dispatcher = new ErrorCatchingDispatcher($dispatcher, $this->logger);
        }

        return new TcpServer($dispatcher, $this->logger, $this->tcpAddress);
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
        $this->addHandler(new SessionHandler($this->sessionManager));
    }
}
