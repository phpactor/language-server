<?php

namespace Phpactor\LanguageServer\Test;

use Amp\Promise;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;

class HandlerTester
{
    const REQUEST_ID = 1;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var MiddlewareDispatcher
     */
    private $middlewareDispatcher;


    public function __construct(Handler $handler)
    {
        $this->handler = $handler;

        $handlers = new Handlers([$this->handler]);
        $runner = new HandlerMethodRunner(
            $handlers,
            new ChainArgumentResolver(
                new LanguageSeverProtocolParamsResolver(),
                new DTLArgumentResolver(),
                new PassThroughArgumentResolver()
            )
        );
        $this->middlewareDispatcher = new MiddlewareDispatcher(
            new CancellationMiddleware($runner),
            new HandlerMiddleware($runner)
        );
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(string $methodName, array $params): Promise
    {
        return $this->middlewareDispatcher->dispatch(new RequestMessage(self::REQUEST_ID, $methodName, $params));
    }

    public function dispatchAndWait(string $methodName, array $params): ?ResponseMessage
    {
        return \Amp\Promise\wait($this->dispatch($methodName, $params));
    }

    public function cancel(): void
    {
        $this->dispatchAndWait('$/cancelRequest', [
            'id' => self::REQUEST_ID
        ]);
    }
}
