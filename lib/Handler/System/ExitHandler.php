<?php

namespace Phpactor\LanguageServer\Handler\System;

use Amp\Delayed;
use Amp\Promise;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Event\WillShutdown;
use Psr\EventDispatcher\EventDispatcherInterface;
use function Amp\call;

class ExitHandler implements Handler
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher = new NullEventDispatcher(),
        private int $gracePeriod = 500,
    ) {
    }

    public function methods(): array
    {
        return [
            'shutdown' => 'shutdown',
            'exit' => 'exit',
        ];
    }

    /**
     * @return Promise<null>
     */
    public function shutdown(): Promise
    {
        return call(function () {
            $this->eventDispatcher->dispatch(new WillShutdown());
            yield new Delayed($this->gracePeriod);
        });
    }

    public function exit(): void
    {
        throw new ExitSession('Exit method invoked by client');
    }
}
