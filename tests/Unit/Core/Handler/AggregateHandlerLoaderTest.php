<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use LanguageServerProtocol\InitializeParams;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\AggregateHandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handlers;

class AggregateHandlerLoaderTest extends TestCase
{
    /**
     * @var InitializeParams
     */
    private $params;

    /**
     * @var ObjectProphecy
     */
    private $loader1;

    /**
     * @var ObjectProphecy
     */
    private $loader2;

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
        $this->params = new InitializeParams();

        $this->loader1 = $this->prophesize(HandlerLoader::class);
        $this->loader2 = $this->prophesize(HandlerLoader::class);

        $this->handler1 = $this->prophesize(Handler::class);
        $this->handler2 = $this->prophesize(Handler::class);
    }

    public function testNoHandlersNoProblem()
    {
        $loader = new AggregateHandlerLoader([]);
        $handlers = $loader->load($this->params);
        $this->assertInstanceOf(Handlers::class, $handlers);
        $this->assertCount(0, $handlers);
    }

    public function testAggregatesLoaders()
    {
        $this->handler1->methods()->willReturn(['one' => 1]);
        $this->handler2->methods()->willReturn(['two' => 2]);

        $loader = new AggregateHandlerLoader([
            $this->loader1->reveal(),
            $this->loader2->reveal()
        ]);

        $this->loader1->load($this->params)->willReturn(new Handlers([$this->handler1->reveal()]));
        $this->loader2->load($this->params)->willReturn(new Handlers([$this->handler2->reveal()]));

        $handlers = $loader->load($this->params);

        $this->assertInstanceOf(Handlers::class, $handlers);
        $this->assertCount(2, $handlers);

        $this->assertEquals(['one', 'two'], $handlers->methods());
    }
}
