<?php

namespace Phpactor\LanguageServer\Core\Server;

use Generator;
use LanguageServerProtocol\InitializeParams;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerCollection;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry\ChainHandlerRegistry;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerLoader;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry\Handlers;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

final class ApplicationContainer implements Handler
{
    /**
     * @var HandlerCollection
     */
    private $serverHandlers;

    /**
     * @var HandlerCollection
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
     * @var HandlerCollection
     */
    private $defaultHandlers;

    /**
     * @var EventEmitter
     */
    private $emitter;

    public function __construct(
        Dispatcher $dispatcher,
        HandlerCollection $serverHandlers,
        HandlerLoader $applicationHandlerLoader
    ) {
        $this->serverHandlers = $serverHandlers;
        $this->applicationHandlerLoader = $applicationHandlerLoader;
        $this->dispatcher = $dispatcher;
        $this->defaultHandlers = new Handlers([
            'initialize' => $this,
            'initialized' => $this,
        ]);
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

        foreach ($this->handlers() as $handler) {
            if (!$handler instanceof CanRegisterCapabilities) {
                continue;
            }

            $handler->registerCapabiltiies($capabilities);
        }

        $result = new InitializeResult();
        $result->capabilities = $capabilities;

        yield $result;
    }

    public function initialized(): void
    {
        // nothing to see here
    }

    /**
     * @return HandlerCollection
     */
    private function handlers(): HandlerCollection
    {
        $handlers = [
            $this->defaultHandlers,
            $this->serverHandlers
        ];

        if ($this->applicationHandlers !== null) {
            $handlers[] = $this->applicationHandlers;
        }

        return new ChainHandlerRegistry($handlers);
    }
}
