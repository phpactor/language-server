<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use LanguageServerProtocol\ServerCapabilities;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handlers;

class ExtensionsTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $extension1;
    /**
     * @var ObjectProphecy
     */
    private $extension2;
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
        $this->extension1 = $this->prophesize(Extension::class);
        $this->extension2 = $this->prophesize(Extension::class);
        $this->handler1 = $this->prophesize(Handler::class);
        $this->handler2 = $this->prophesize(Handler::class);
        $this->handler1->name()->willReturn('one');
        $this->handler2->name()->willReturn('two');
    }

    public function testReturnsNoHandlersWhenNoExtensions()
    {
        $extensions = new Extensions([]);
        $this->assertEquals(new Handlers(), $extensions->handlers());
    }

    public function testReturnsAggregatedHandlers()
    {
        $extensions = new Extensions([
            $this->extension1->reveal(),
            $this->extension2->reveal(),
        ]);
        $this->extension1->handlers()->willReturn(new Handlers([
            $this->handler1->reveal(),
        ]));
        $this->extension2->handlers()->willReturn(new Handlers([
            $this->handler2->reveal(),
        ]));

        $this->assertEquals(new Handlers([
            $this->handler1->reveal(),
            $this->handler2->reveal()
        ]), $extensions->handlers());
    }

    public function testConfiguresServerCapabilities()
    {
        $capabilities = new ServerCapabilities();
        $extensions = new Extensions([
            $this->extension1->reveal(),
        ]);
        $this->extension1->configureCapabilities($capabilities)->will(function ($args) {
            $args[0]->textDocumentSync = 1;
        });
        $extensions->configureCapabilities($capabilities);

        $this->assertEquals(1, $capabilities->textDocumentSync);
    }
}
