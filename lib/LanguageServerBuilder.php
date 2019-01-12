<?php

namespace Phpactor\LanguageServer;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\RecordingDispatcher;
use Phpactor\LanguageServer\Core\Handler\AggregateHandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Server\ApplicationContainer;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Test\ServerTester;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

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
    private $handlerLoaders = [];

    /**
     * @var string
     */
    private $recordTo;

    private function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Create a new instance of the builder \o/
     */
    public static function create(
        LoggerInterface $logger = null
    ): self {
        return new self(
            $logger ?: new NullLogger()
        );
    }

    /**
     * Log any exceptions are thrown when handling requests and continue.
     */
    public function catchExceptions(bool $enabled = true): self
    {
        $this->catchExceptions = $enabled;

        return $this;
    }

    /**
     * Start the event loop when the server starts.
     */
    public function eventLoop(bool $enabled = true): self
    {
        $this->eventLoop = $enabled;

        return $this;
    }

    /**
     * Add a handler that will be registered at the system (server) level.
     * Such handlers will bem general to all connections made to the server and
     * is not connection (session) specific.
     *
     * For sessiaon specific handlers. See LanguageBuilder#addHandlerLoader.
     */
    public function addSystemHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Handler loaders are used to instantiate handlers for a new
     * connection/session.
     *
     * Such handlers include the TextDocumentHandler which requires a clean
     * workspace when a new connection/session is started. Another example
     * might be the CompletionHandler which has dependencies which in turn
     * depend on the initialized project root directory.
     */
    public function addHandlerLoader(HandlerLoader $loader): self
    {
        $this->handlerLoaders[] = $loader;

        return $this;
    }

    /**
     * Start a TCP server on the given address.
     *
     * The TCP server can handle multiple connections/sessions, but must be
     * started manually before clients can connect to it.
     *
     * The TCP server is valuable for development and for debugging as it echos
     * the debug information to STDERR.
     *
     * Note that the default behavior is to start a STDIO server.
     */
    public function tcpServer(?string $address = '0.0.0.0:0'): self
    {
        $this->tcpAddress = $address;

        return $this;
    }

    public function recordTo(string $filename): self
    {
        $this->recordTo = $filename;

        return $this;
    }

    /**
     * Build the language server.
     *
     * The returned language server instance can then be started by calling
     * start().
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

        $handlers = $this->buildHandlers();

        return new LanguageServer(
            $dispatcher,
            $handlers,
            $this->buildHandlerLoader(),
            $this->logger,
            $provider,
            $this->eventLoop
        );
    }

    public function buildServerTester(): ServerTester
    {
        return new ServerTester(new ApplicationContainer(
            $this->buildDispatcher(),
            $this->buildHandlers(),
            $this->buildHandlerLoader()
        ));
    }

    private function buildDispatcher(): Dispatcher
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

        if ($this->recordTo) {
            $recordResource = fopen($this->recordTo, 'w');

            if (false === $recordResource) {
                throw new RuntimeException(sprintf(
                    'Could not open recording file "%s"',
                    $this->recordTo
                ));
            }

            $dispatcher = new RecordingDispatcher(
                $dispatcher,
                new ResourceOutputStream($recordResource)
            );
        }

        return $dispatcher;
    }

    private function buildHandlerLoader(): AggregateHandlerLoader
    {
        return new AggregateHandlerLoader($this->handlerLoaders);
    }

    private function buildHandlers(): Handlers
    {
        return new Handlers($this->handlers);
    }
}
