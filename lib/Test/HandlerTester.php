<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
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

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
        $this->messageTransmitter = new TestMessageTransmitter();
    }

    public function transmitter(): TestMessageTransmitterStack
    {
        return $this->messageTransmitter;
    }

    public function dispatch(string $methodName, array $params)
    {
        $this->messageTransmitter = new TestMessageTransmitter();

        $extraArgs = [
            $this->messageTransmitter
        ];

        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver()
        );

        $handlers = new Handlers([$this->handler]);

        $request = new RequestMessage(1, $methodName, $params);

        return \Amp\Promise\wait($dispatcher->dispatch($handlers, $request, $extraArgs));
    }
}
