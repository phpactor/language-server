<?php

namespace Phpactor\LanguageServer\Adapter\Psr;

use Psr\EventDispatcher\EventDispatcherInterface;

final class NullEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event)
    {
        return $event;
    }
}
