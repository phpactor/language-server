<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;
use Phpactor\LanguageServer\Core\Dispatcher\Handlers;

class HandlersTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $handler;

    public function setUp()
    {
        $this->handler = $this->prophesize(Handler::class);
    }

    public function testThrowsExceptionNotFound()
    {
        $this->expectException(HandlerNotFound::class);
        $this->handler->name()->willReturn('barbra');
        $registry = $this->create([ $this->handler->reveal() ]);
        $registry->get('foobar');
    }

    public function testReturnsHandler()
    {
        $this->handler->name()->willReturn('foobar');
        $registry = $this->create([ $this->handler->reveal() ]);
        $handler = $registry->get('foobar');
        $this->assertSame($this->handler->reveal(), $handler);
    }

    private function create(array $handlers)
    {
        return new Handlers($handlers);
    }
}
