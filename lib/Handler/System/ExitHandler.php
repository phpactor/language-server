<?php

namespace Phpactor\LanguageServer\Handler\System;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Event\WillShutdown;
use Psr\EventDispatcher\EventDispatcherInterface;

class ExitHandler implements Handler
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher ?: new NullEventDispatcher();
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
        $this->eventDispatcher->dispatch(new WillShutdown());
        return new Success(null);
    }

    public function exit(): void
    {
        throw new ExitSession('Exit method invoked by client');
    }
}
