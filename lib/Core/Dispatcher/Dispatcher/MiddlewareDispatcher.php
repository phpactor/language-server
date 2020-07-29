<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;

final class MiddlewareDispatcher implements Dispatcher
{
    /**
     * @var array
     */
    private $middleware;

    public function __construct(Middleware ...$middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(Message $request): Promise
    {
        $handler = new RequestHandler($this->middleware);
        return $handler->handle($request);
    }
}
