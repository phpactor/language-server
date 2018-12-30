<?php

namespace Phpactor\LanguageServer\Core\Event;

interface EventSubscriber
{
    /**
     * Reutrn array of:
     *
     *     event-name => instance method name
     *
     * For example:
     *
     *    return [
     *        LanguageServerEvents::CAPABILITIES_REGISTER => 'registerCapabilities'
     *    ];
     */
    public function events(): array;
}
