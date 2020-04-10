<?php

namespace Phpactor\LanguageServer\Test;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitterStack;

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

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
        $this->messageTransmitter = new TestMessageTransmitter();
        $this->cancellationTokenSource = new CancellationTokenSource();
    }

    public function transmitter(): TestMessageTransmitterStack
    {
        return $this->messageTransmitter;
    }

    public function cancel(): void
    {
        $this->cancellationTokenSource->cancel();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(string $methodName, array $params): Promise
    {
        $this->messageTransmitter = new TestMessageTransmitter();
        $this->cancellationTokenSource = new CancellationTokenSource();

        $extraArgs = [
            '_transmitter' => $this->messageTransmitter,
            '_token' => $this->cancellationTokenSource->getToken()
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
}
