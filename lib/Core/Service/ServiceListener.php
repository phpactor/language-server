<?php

namespace Phpactor\LanguageServer\Core\Service;

use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;

class ServiceListener implements ListenerProviderInterface
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            yield function (Initialized $closed) {
                $this->serviceManager->startAll();
            };
            return;
        }
    }
}
