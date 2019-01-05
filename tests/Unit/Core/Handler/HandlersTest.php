<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Handler\Handlers;

class HandlersTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $handler1;

    /**
     * @var ObjectProphecy
     */
    private $handler2;

    public function setUp()
    {
        $this->handler1 = $this->prophesize(Handler::class);
        $this->handler2 = $this->prophesize(Handler::class);
    }

    public function testThrowsExceptionNotFound()
    {
        $this->expectException(HandlerNotFound::class);
        $this->handler1->methods()->willReturn(['barbra']);
        $registry = $this->create([ $this->handler1->reveal() ]);
        $registry->get('foobar');
    }

    public function testReturnsHandler()
    {
        $this->handler1->methods()->willReturn(['foobar' => 'foobar']);
        $registry = $this->create([ $this->handler1->reveal() ]);
        $handler = $registry->get('foobar');
        $this->assertSame($this->handler1->reveal(), $handler);
    }

    public function testMerge()
    {
        $this->handler1->methods()->willReturn(['foobar' => 'foobar']);
        $this->handler2->methods()->willReturn(['barfoo' => 'barfoo']);

        $handlers = $this->create([ $this->handler1->reveal() ]);
        $handlers->merge($this->create([ $this->handler2->reveal() ]));

        $this->assertCount(2, $handlers);
        $this->assertEquals(['foobar', 'barfoo'], $handlers->methods());
    }

    private function create(array $handlers): Handlers
    {
        return new Handlers($handlers);
    }
}
