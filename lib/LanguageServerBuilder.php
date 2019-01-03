<?php

namespace Phpactor\LanguageServer;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Closure;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
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

    /**
     * @var array
     */
    private $factories = [];

    private function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public static function create(
        LoggerInterface $logger = null
    ): self {
        return new self(
            $logger ?: new NullLogger()
        );
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

    public function addHandlerFactory(string $method, Closure $closure): self
    {
        $this->factories[$method] = $closure;

        return $this;
    }

    public function addHandler(Handler $handler): self
    {
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

        $handlers = new Handlers($this->handlers);

        return new LanguageServer(
            $dispatcher,
            $handlers,
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
        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver()
        );

        if ($this->catchExceptions) {
            $dispatcher = new ErrorCatchingDispatcher(
                $dispatcher,
                $this->logger
            );
        }

        return $dispatcher;
    }
}
