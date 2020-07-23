<?php

namespace Phpactor\LanguageServer;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\CancellingMethodDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\RecordingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ResponseDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\AggregateHandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Server\ApplicationContainer;
use Phpactor\LanguageServer\Core\Server\Initializer\RequestInitializer;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\SessionServices;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\StreamProvider\SocketStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
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
     * @var string|null
     */
    private $tcpAddress = null;

    /**
     * @var DispatcherFactory
     */
    private $dispatcherFactory;

    /**
     * @var ServerStats|null
     */
    private $stats = null;

    private function __construct(
        DispatcherFactory $dispatcherFactory,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->dispatcherFactory = $dispatcherFactory;
    }

    /**
     * Create a new instance of the builder \o/
     */
    public static function create(
        DispatcherFactory $dispatcherFactory,
        LoggerInterface $logger = null
    ): self {
        return new self(
            $dispatcherFactory,
            $logger ?: new NullLogger()
        );
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

    /**
     * Build the language server.
     *
     * The returned language server instance can then be started by calling
     * start().
     */
    public function build(): LanguageServer
    {
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
            $this->dispatcherFactory,
            $this->logger,
            $provider,
            new RequestInitializer(),
            $this->stats ?: new ServerStats()
        );
    }

    public function withServerStats(ServerStats $stats): self
    {
        $this->stats = $stats;

        return $this;
    }
}
