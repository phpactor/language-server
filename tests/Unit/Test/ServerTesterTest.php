<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use LanguageServerProtocol\TextDocumentItem;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;

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
        $workspace = new Workspace();
        $builder->addSystemHandler(new TextDocumentHandler($workspace));
        $tester = $builder->buildServerTester();
        $tester->initialize();
        $response = $tester->openDocument(new TextDocumentItem('file://foobar', 'some text'));
        $this->assertCount(1, $workspace);
    }
}
