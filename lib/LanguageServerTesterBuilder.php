<?php

namespace Phpactor\LanguageServer;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Psr\AggregateEventDispatcher;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use Phpactor\LanguageServer\Core\Diagnostics\AggregateDiagnosticsProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Handler\Workspace\DidChangeWatchedFilesHandler;
use Phpactor\LanguageServer\Listener\DidChangeWatchedFilesListener;
use Phpactor\LanguageServer\Listener\ServiceListener;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Service\DiagnosticsService;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\NullLogger;

final class LanguageServerTesterBuilder
{
    /**
     * @var array<Handler>
     */
    private $handlers = [];

    /**
     * @var array
     */
    private $serviceProviders = [];

    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var InitializeParams
     */
    private $initializeParams;

    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    /**
     * @var TestResponseWatcher
     */
    private $responseWatcher;

    /**
     * @var bool
     */
    private $enableTextDocuments = false;

    /**
     * @var ResponseWatcher
     */
    private $responseHandler;

    /**
     * @var RpcClient
     */
    private $rpcClient;

    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var bool
     */
    private $enableServices = false;

    /**
     * @var bool
     */
    private $enableFileEvents = false;

    /**
     * @var bool
     */
    private $enableDiagnostics = false;

    /**
     * @var bool
     */
    private $enableCommands = false;

    /**
     * @var array<ListenerProviderInterface>
     */
    private $listeners = [];

    /**
     * @var array<DiagnosticsProvider>
     */
    private $diagnosticsProvider = [];

    /**
     * @var string[]
     */
    private $fileEventGlobs = ['**/*.php'];

    private function __construct()
    {
        $this->initializeParams = new InitializeParams(new ClientCapabilities());
        $this->transmitter = new TestMessageTransmitter();
        $this->responseWatcher = new TestResponseWatcher(new DeferredResponseWatcher());
        $this->rpcClient = new JsonRpcClient($this->transmitter, $this->responseWatcher);
        $this->clientApi = new ClientApi($this->rpcClient);
        $this->workspace = new Workspace();
    }

    /**
     * Create a new tester with optional services enabled.
     */
    public static function create(): self
    {
        $tester = new self();
        $tester->enableTextDocuments();
        $tester->enableServices();
        $tester->enableDiagnostics();
        $tester->enableCommands();

        return $tester;
    }

    /**
     * Return a minimum tester without optional services.
     */
    public static function createBare(): self
    {
        return new self();
    }

    /**
     * Set the initialization parameters whcih will be used by the tester
     */
    public function setInitializeParams(InitializeParams $params): self
    {
        $this->initializeParams = $params;

        return $this;
    }

    /**
     * Add a method handler
     */
    public function addHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Add a service provider
     */
    public function addServiceProvider(ServiceProvider $serviceProvider): self
    {
        $this->serviceProviders[] = $serviceProvider;

        return $this;
    }

    public function addDiagnosticsProvider(DiagnosticsProvider $diagnosticsProvider): self
    {
        $this->diagnosticsProvider[] = $diagnosticsProvider;

        return $this;
    }

    public function addListenerProvider(ListenerProviderInterface $listenerProvider): self
    {
        $this->listeners[] = $listenerProvider;

        return $this;
    }

    /**
     * Add an command
     */
    public function addCommand(string $commandId, Command $command): self
    {
        $this->commands[$commandId] = $command;

        return $this;
    }

    /**
     * Enable the text document service (enabled by default with ::create)
     */
    public function enableTextDocuments(): self
    {
        $this->enableTextDocuments = true;

        return $this;
    }

    /**
     * Enable file events
     * @param string[] $globs
     */
    public function enableFileEvents(?array $globs = null): self
    {
        $this->enableFileEvents = true;

        if (null !==$globs) {
            $this->fileEventGlobs = $globs;
        }

        return $this;
    }

    /**
     * Enable the services (enabled by default with ::create)
     */
    public function enableServices(): self
    {
        $this->enableServices = true;

        return $this;
    }

    public function enableDiagnostics(): self
    {
        $this->enableDiagnostics = true;
        return $this;
    }
    
    public function enableCommands(): self
    {
        $this->enableCommands = true;
        return $this;
    }

    /**
     * Test Transmitter service: can be used to check which
     * messages have been sent.
     */
    public function transmitter(): TestMessageTransmitter
    {
        return $this->transmitter;
    }

    /**
     * ClientApi service
     */
    public function clientApi(): ClientApi
    {
        return $this->clientApi;
    }

    /**
     * RPC client service
     */
    public function rpcClient(): RpcClient
    {
        return $this->rpcClient;
    }

    /**
     * Workspace service (access to text documents)
     */
    public function workspace(): Workspace
    {
        return $this->workspace;
    }

    public function responseWatcher(): TestResponseWatcher
    {
        return $this->responseWatcher;
    }

    public function build(): LanguageServerTester
    {
        return new LanguageServerTester(
            $this->buildDisapatcherFactory(),
            $this->initializeParams,
            $this->transmitter
        );
    }

    private function buildDisapatcherFactory(): DispatcherFactory
    {
        return new ClosureDispatcherFactory(
            function (MessageTransmitter $transmitter, InitializeParams $params) {
                $logger =  new NullLogger();
                
                $serviceProviders = $this->serviceProviders;

                if ($this->enableDiagnostics) {
                    $service = new DiagnosticsService(
                        new DiagnosticsEngine(
                            $this->clientApi,
                            new AggregateDiagnosticsProvider($logger, ...$this->diagnosticsProvider),
                            0
                        ),
                    );
                    $serviceProviders[] = $service;
                    $this->listeners[] = $service;
                }

                if ($this->enableFileEvents) {
                    $this->listeners[] = new DidChangeWatchedFilesListener($this->clientApi, $this->fileEventGlobs, $params->capabilities);
                }

                $serviceManager = new ServiceManager(new ServiceProviders(...$serviceProviders), $logger);
                $eventDispatcher = $this->buildEventDispatcher($serviceManager);

                $handlers = $this->handlers;

                if ($this->enableTextDocuments) {
                    $handlers[] = new TextDocumentHandler($eventDispatcher);
                }

                if ($this->enableServices) {
                    $handlers[] = new ServiceHandler($serviceManager, $this->clientApi);
                }

                if ($this->enableFileEvents) {
                    $handlers[] = new DidChangeWatchedFilesHandler($eventDispatcher);
                }

                if ($this->enableCommands) {
                    $handlers[] = new CommandHandler(new CommandDispatcher($this->commands));
                }

                $handlers = new Handlers(...$handlers);

                $runner = new HandlerMethodRunner(
                    $handlers,
                    new ChainArgumentResolver(
                        new LanguageSeverProtocolParamsResolver(),
                        new DTLArgumentResolver(),
                        new PassThroughArgumentResolver()
                    )
                );

                return new MiddlewareDispatcher(
                    new InitializeMiddleware($handlers, $eventDispatcher),
                    new CancellationMiddleware($runner),
                    new HandlerMiddleware($runner)
                );
            }
        );
    }
    private function buildEventDispatcher(ServiceManager $serviceManager): EventDispatcherInterface
    {
        $listeners = $this->listeners;

        if ($this->enableServices) {
            $listeners[] = new ServiceListener($serviceManager);
        }
        
        if ($this->enableTextDocuments) {
            $listeners[] = new WorkspaceListener($this->workspace);
        }
        
        return new AggregateEventDispatcher(...$listeners);
    }
}
