<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;

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

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(string $methodName, array $params): Promise
    {
        $handlers = new Handlers([$this->handler]);
        $request = new RequestMessage(1, $methodName, $params);
        $middlewareDispatcher = new MiddlewareDispatcher(
            new HandlerMiddleware(
                new HandlerMethodRunner(
                    $handlers,
                    new HandlerMethodResolver(),
                    new ChainArgumentResolver(
                        new LanguageSeverProtocolParamsResolver(),
                        new DTLArgumentResolver(),
                        new PassThroughArgumentResolver()
                    )
                )
            )
        );

        return $middlewareDispatcher->dispatch($request);
    }

    public function dispatchAndWait(string $methodName, array $params)
    {
        return \Amp\Promise\wait($this->dispatch($methodName, $params));
    }
}
