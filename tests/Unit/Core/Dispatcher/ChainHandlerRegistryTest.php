<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ChainHandlerRegistry;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry;

class ChainHandlerRegistryTest extends TestCase
{
    const EXAMPLE_NAME = 'handler_1';

    /**
     * @var ObjectProphecy
     */
    private $registry1;
    /**
     * @var ObjectProphecy
     */
    private $registry2;

    public function setUp()
    {
        $this->registry1 = $this->prophesize(HandlerRegistry::class);
        $this->registry2 = $this->prophesize(HandlerRegistry::class);

        $this->handler = $this->prophesize(Handler::class);
    }

    public function testReturnsFirstAvailableHandler()
    {
        $registry = new ChainHandlerRegistry([
            $this->registry1->reveal(),
            $this->registry2->reveal()
        ]);

        $this->registry1->get(self::EXAMPLE_NAME)->willThrow(new HandlerNotFound('asd'));
        $this->registry2->get(self::EXAMPLE_NAME)->willReturn($this->handler->reveal());

        $handler = $registry->get(self::EXAMPLE_NAME);
        $this->assertSame($this->handler->reveal(), $handler);
    }

    public function testThrowsExceptionIfNoHandlerFound()
    {
        $this->expectException(HandlerNotFound::class);
        $registry = new ChainHandlerRegistry([
            $this->registry1->reveal(),
            $this->registry2->reveal()
        ]);

        $this->registry1->get(self::EXAMPLE_NAME)->willThrow(new HandlerNotFound('asd'));
        $this->registry2->get(self::EXAMPLE_NAME)->willThrow(new HandlerNotFound('asd'));

        $registry->get(self::EXAMPLE_NAME);
    }
}
