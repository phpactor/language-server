<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\InitializeResult;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\Message;

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
     * @var SessionServices
     */
    private $sessionServices;

    public function __construct(
        Dispatcher $dispatcher,
        Handlers $serverHandlers,
        HandlerLoader $applicationHandlerLoader,
        SessionServices $sessionService
    ) {
        $this->serverHandlers = $serverHandlers;
        $this->applicationHandlerLoader = $applicationHandlerLoader;
        $this->dispatcher = $dispatcher;
        $this->defaultHandlers = new Handlers([$this]);
        $this->sessionServices = $sessionService;
    }

    /**
     * @return Promise<Message|null>
     */
    public function dispatch(Message $message, array $extraArgs): Promise
    {
        return $this->dispatcher->dispatch($this->handlers(), $message, $extraArgs);
    }

    public function methods(): array
    {
        return [
            'initialize' => 'initialize',
            'initialized' => 'initialized',
        ];
    }

    /**
     * @return Promise<InitializeResult>
     */
    public function initialize(InitializeParams $params): Promise
    {
        $this->applicationHandlers = $this->applicationHandlerLoader->load($params, $this->sessionServices);

        $capabilities = new ServerCapabilities();
        $capabilities->codeActionProvider = false;

        foreach ($this->handlers()->methods() as $handler) {
            if (!$handler instanceof CanRegisterCapabilities) {
                continue;
            }

            $handler->registerCapabiltiies($capabilities);
        }

        $result = new InitializeResult($capabilities);

        return new Success($result);
    }

    /**
     * @return Promise<null>
     */
    public function initialized(): Promise
    {
        $this->startServices();
        return new Success(null);
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

    private function startServices(): void
    {
        foreach ($this->handlers()->services() as $handler) {
            if (!$handler instanceof ServiceProvider) {
                continue;
            }
        
            $this->sessionServices->serviceManager()->register(
                $handler
            );
        }
        
        $this->sessionServices->serviceManager()->startAll();
    }
}
