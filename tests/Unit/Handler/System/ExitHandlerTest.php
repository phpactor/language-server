<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Event\WillShutdown;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class ExitHandlerTest extends HandlerTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EventDispatcherInterface>
     */
    private ObjectProphecy $eventDispatcher;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    }


    public function handler(): Handler
    {
        return new ExitHandler($this->eventDispatcher->reveal());
    }

    public function testThrowsExitSessionException(): void
    {
        $this->expectException(ExitSession::class);
        $this->dispatch('exit', []);
    }

    public function testShutdownDoesNothing(): void
    {
        $this->dispatch('shutdown', []);
        $this->eventDispatcher->dispatch(new WillShutdown())->shouldHaveBeenCalled();
    }
}
