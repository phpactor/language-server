<?php

namespace Phpactor\LanguageServer\Adapter\Psr;

use Psr\EventDispatcher\EventDispatcherInterface;

class NullEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event)
    {
        return $event;
    }
}
