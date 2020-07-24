<?php

namespace Phpactor\LanguageServer\Core\Service;

use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;

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
