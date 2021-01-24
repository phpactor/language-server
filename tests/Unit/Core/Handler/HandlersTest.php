<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Phpactor\TestUtils\PHPUnit\TestCase;
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

    protected function setUp(): void
    {
        $this->handler1 = $this->prophesize(Handler::class);
        $this->handler2 = $this->prophesize(Handler::class);
    }

    public function testThrowsExceptionNotFound(): void
    {
        $this->expectException(HandlerNotFound::class);
        $this->handler1->methods()->willReturn(['barbra']);
        $handlers = $this->create([ $this->handler1->reveal() ]);
        $handlers->get('foobar');
    }

    public function testReturnsHandler(): void
    {
        $this->handler1->methods()->willReturn(['foobar' => 'foobar']);
        $handlers = $this->create([ $this->handler1->reveal() ]);
        $handler = $handlers->get('foobar');
        $this->assertSame($this->handler1->reveal(), $handler);
    }

    public function testMerge(): void
    {
        $this->handler1->methods()->willReturn(['foobar' => 'foobar']);
        $this->handler2->methods()->willReturn(['barfoo' => 'barfoo']);

        $handlers = $this->create([ $this->handler1->reveal() ]);
        $handlers->merge($this->create([ $this->handler2->reveal() ]));

        $this->assertCount(2, $handlers);
        $this->assertEquals(['foobar', 'barfoo'], array_keys($handlers->methods()));
    }

    private function create(array $handlers): Handlers
    {
        return new Handlers(...$handlers);
    }
}
