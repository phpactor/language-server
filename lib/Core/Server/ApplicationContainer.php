<?php

namespace Phpactor\LanguageServer\Core\Server;

use Generator;
use LanguageServerProtocol\InitializeParams;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Service\ServiceManager;

final class ApplicationContainer implements Handler
{
    /**
     * @var Handlers
     */
    private $defaultHandlers;

    /**
     * @var Handlers
     */
    private $serverHandlers;

    /**
     * @var Handlers
     */
    private $applicationHandlers;

    /**
     * @var HandlerLoader
     */
    private $applicationHandlerLoader;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    public function __construct(
        Dispatcher $dispatcher,
        Handlers $serverHandlers,
        HandlerLoader $applicationHandlerLoader,
        ServiceManager $serviceManager
    ) {
        $this->serverHandlers = $serverHandlers;
        $this->applicationHandlerLoader = $applicationHandlerLoader;
        $this->dispatcher = $dispatcher;
        $this->defaultHandlers = new Handlers([$this]);
        $this->serviceManager = $serviceManager;
    }

    public function dispatch(RequestMessage $message): Generator
    {
        yield from $this->dispatcher->dispatch($this->handlers(), $message);
    }

    public function methods(): array
    {
        return [
            'initialize' => 'initialize',
            'initialized' => 'initialized',
        ];
    }

    public function initialize(
        array $capabilities = [],
        array $initializationOptions = [],
        ?int $processId = null,
        ?string $rootPath = null,
        ?string $rootUri = null,
        ?string $trace = null
    ) {
        $this->applicationHandlers = $this->applicationHandlerLoader->load(
            new InitializeParams(
                $capabilities,
                $initializationOptions,
                $processId,
                $rootPath,
                $rootUri,
                $trace
            )
        );

        $capabilities = new ServerCapabilities();

        foreach ($this->handlers()->methods() as $handler) {
            if (!$handler instanceof CanRegisterCapabilities) {
                continue;
            }

            $handler->registerCapabiltiies($capabilities);
        }

        $result = new InitializeResult();
        $result->capabilities = $capabilities;

        $this->startServices();

        yield $result;
    }

    public function initialized(): void
    {
    }

    /**
     * @return Handlers
     */
    private function handlers(): Handlers
    {
        $handlers = new Handlers();
        $handlers->merge($this->defaultHandlers);
        $handlers->merge($this->serverHandlers);

        if ($this->applicationHandlers !== null) {
            $handlers->merge($this->applicationHandlers);
        }

        return $handlers;
    }

    private function startServices()
    {
        foreach ($this->handlers()->services() as $handler) {
            if (!$handler instanceof ServiceProvider) {
                continue;
            }
        
            $this->serviceManager->register(
                $handler,
                $handler->services()
            );
        }
        
        $this->serviceManager->start();
    }
}
