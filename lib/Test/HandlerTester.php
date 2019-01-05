<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class HandlerTester
{
    /**
     * @var Handler
     */
    private $handler;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function dispatch(string $methodName, array $params): array
    {
        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver()
        );
        $handlers = new Handlers([$this->handler]);

        $request = new RequestMessage(1, $methodName, $params);

        return iterator_to_array($dispatcher->dispatch($handlers, $request));
    }
}
