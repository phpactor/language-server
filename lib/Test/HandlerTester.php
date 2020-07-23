<?php

namespace Phpactor\LanguageServer\Test;

use Amp\CancellationTokenSource;
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
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitterStack;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Psr\Log\NullLogger;

class HandlerTester
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var CancellationTokenSource
     */
    private $cancellationTokenSource;

    /**
     * @var ResponseWatcher
     */
    private $responseWatcher;

    /**
     * @var RpcClient
     */
    private $serverClient;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
        $this->cancellationTokenSource = new CancellationTokenSource();
        $this->responseWatcher = new TestResponseWatcher();
    }

    public function cancel(): void
    {
        $this->cancellationTokenSource->cancel();
    }

    public function responseWatcher(): ResponseWatcher
    {
        return $this->responseWatcher;
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
