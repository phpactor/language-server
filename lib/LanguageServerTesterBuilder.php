<?php

namespace Phpactor\LanguageServer;

use Phly\EventDispatcher\EventDispatcher;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\System\StatsHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Listener\ServiceListener;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

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

    private function __construct()
    {
        $this->initializeParams = new InitializeParams(new ClientCapabilities());
    }

    public static function create(): self
    {
        return new self();
    }

    public function setInitializeParams(InitializeParams $params): self
    {
        $this->initializeParams = $params;

        return $this;
    }

    public function addHandler(Handler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function addServiceProvider(ServiceProvider $serviceProvider): self
    {
        $this->serviceProviders[] = $serviceProvider;

        return $this;
    }

    public function addCommand(string $commandId, Command $command): self
    {
        $this->commands[$commandId] = $command;

        return $this;
    }

    public function build(): LanguageServerTester
    {
        return new LanguageServerTester(
            $this->buildDisapatcherFactory(),
            $this->initializeParams
        );
    }

    private function buildDisapatcherFactory(): DispatcherFactory
    {
        return new ClosureDispatcherFactory(
            function (MessageTransmitter $transmitter, InitializeParams $params) {
                $logger =  new NullLogger();
                $serviceManager = new ServiceManager(new ServiceProviders(...$this->serviceProviders), $logger);

                $runner = new HandlerMethodRunner(
                    new Handlers(...$this->handlers),
                    new ChainArgumentResolver(
                        new LanguageSeverProtocolParamsResolver(),
                        new DTLArgumentResolver(),
                        new PassThroughArgumentResolver()
                    ),
                );

                return new MiddlewareDispatcher(
                    new CancellationMiddleware($runner),
                    new HandlerMiddleware($runner)
                );
            }
        );
    }
}
