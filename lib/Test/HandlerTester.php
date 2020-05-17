<?php

namespace Phpactor\LanguageServer\Test;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitterStack;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Psr\Log\NullLogger;

class HandlerTester
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var TestMessageTransmitter
     */
    private $messageTransmitter;

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

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
        $this->messageTransmitter = new TestMessageTransmitter();
        $this->cancellationTokenSource = new CancellationTokenSource();
        $this->responseWatcher = new TestResponseWatcher();
        $this->serverClient = new JsonRpcClient($this->messageTransmitter, $this->responseWatcher);
        $this->serviceManager = new ServiceManager($this->messageTransmitter, new NullLogger(), new DTLArgumentResolver());

        if ($handler instanceof ServiceProvider) {
            $this->serviceManager->register($handler);
        }
    }

    public function transmitter(): TestMessageTransmitterStack
    {
        return $this->messageTransmitter;
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
        $this->cancellationTokenSource = new CancellationTokenSource();

        $extraArgs = [
            '_transmitter' => $this->messageTransmitter,
            '_token' => $this->cancellationTokenSource->getToken(),
            '_serverClient' => $this->serverClient,
            '_serviceManager' => $this->serviceManager,
        ];

        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver()
        );

        $handlers = new Handlers([$this->handler]);

        $request = new RequestMessage(1, $methodName, $params);

        return $dispatcher->dispatch($handlers, $request, $extraArgs);
    }

    public function dispatchAndWait(string $methodName, array $params)
    {
        return \Amp\Promise\wait($this->dispatch($methodName, $params));
    }

    public function serviceManager(): ServiceManager
    {
        return $this->serviceManager;
    }
}
