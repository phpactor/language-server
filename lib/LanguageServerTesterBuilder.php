<?php

namespace Phpactor\LanguageServer;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Psr\AggregateEventDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
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
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Psr\EventDispatcher\EventDispatcherInterface;
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
     * @var ResponseWatcher
     */
    private $responseWatcher;

    /**
     * @var bool
     */
    private $enableTextDocuments = false;

    /**
     * @var DeferredResponseWatcher
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

    private function __construct()
    {
        $this->initializeParams = new InitializeParams(new ClientCapabilities());
        $this->transmitter = new TestMessageTransmitter();
        $this->responseWatcher = new DeferredResponseWatcher();
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
     * Enable the services (enabled by default with ::create)
     */
    public function enableServices(): self
    {
        $this->enableServices = true;

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
                
                $serviceManager = new ServiceManager(new ServiceProviders(...$this->serviceProviders), $logger);
                $eventDispatcher = $this->buildEventDispatcher($serviceManager);

                $handlers = $this->handlers;

                if ($this->enableTextDocuments) {
                    $handlers[] = new TextDocumentHandler($eventDispatcher);
                }

                if ($this->enableServices) {
                    $handlers[] = new ServiceHandler($serviceManager, $this->clientApi);
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
        $listeners = [];

        if ($this->enableServices) {
            $listeners[] = new ServiceListener($serviceManager);
        }
        
        if ($this->enableTextDocuments) {
            $listeners[] = new WorkspaceListener($this->workspace);
        }
        
        return new AggregateEventDispatcher(...$listeners);
    }
}
