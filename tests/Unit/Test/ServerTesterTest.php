<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;

class ServerTesterTest extends TestCase
{
    public function testServerTester()
    {
        $builder = LanguageServerBuilder::create();
        $builder->catchExceptions(false);
        $tester = $builder->buildServerTester();
        $response = $tester->initialize();
        $this->addToAssertionCount(1);
    }

    public function testOpensDocument()
    {
        $builder = LanguageServerBuilder::create();
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $builder->addSystemHandler(new TextDocumentHandler($dispatcher->reveal()));
        $tester = $builder->buildServerTester();
        $tester->initialize();
        $item = new TextDocumentItem('file://foobar', 'some text');
        $tester->openDocument($item);

        $dispatcher->dispatch(new TextDocumentOpened($item))->shouldHaveBeenCalled();
    }
}
