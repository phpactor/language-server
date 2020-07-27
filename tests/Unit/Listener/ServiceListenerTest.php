<?php

namespace Phpactor\LanguageServer\Tests\Unit\Listener;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Listener\ServiceListener;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Event\Initialized;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

class ServiceListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ServiceListener
     */
    private $listener;

    /**
     * @var ObjectProphecy<ServiceManager>
     */
    private $serviceManager;

    protected function setUp(): void
    {
        $this->serviceManager = $this->prophesize(ServiceManager::class);
        $this->listener = (new ServiceListener($this->serviceManager->reveal()));
    }

    public function testStartsServicesonInitialized(): void
    {
        $event = new Initialized();
        $this->dispatch($event);
        $this->serviceManager->startAll()->shouldHaveBeenCalled();
    }

    public function testDoesNotStartServicesOnOtherEvent(): void
    {
        $event = new stdClass();
        $this->dispatch($event);
        $this->serviceManager->startAll()->shouldNotHaveBeenCalled();
    }

    private function dispatch(object $event): void
    {
        foreach ($this->listener->getListenersForEvent($event) as $listener) {
            $listener($event);
        };
    }
}
