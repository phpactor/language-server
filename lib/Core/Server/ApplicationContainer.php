<?php

namespace Phpactor\LanguageServer\Core\Server;

use Generator;
use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerLoader;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use RuntimeException;

final class ApplicationContainer
{
    /**
     * @var HandlerRegistry
     */
    private $serverHandlers;

    /**
     * @var HandlerRegistry
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

    public function __construct(
        Dispatcher $dispatcher,
        HandlerRegistry $serverHandlers,
        HandlerLoader $applicationHandlerLoader
    )
    {
        $this->serverHandlers = $serverHandlers;
        $this->applicationHandlerLoader = $applicationHandlerLoader;
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(RequestMessage $message): Generator
    {
        try {
            yield from $this->dispatcher->dispatch($this->serverHandlers, $message);
            return;
        } catch (HandlerNotFound $exception) {
            yield from $this->dispatcher->dispatch($this->applicationHandlers(), $message);
        }
    }

    public function initialize(InitializeParams $params)
    {
        $this->applicationHandlers = $this->applicationHandlerLoader->load($params);
    }

    private function applicationHandlers(): HandlerRegistry
    {
        if (null === $this->applicationHandlers) {
            throw new RuntimeException(
                'The application has not been initialized, cannot invoke RPC methods (other than "initialize")');
        }

        return $this->applicationHandlers;
    }
}
