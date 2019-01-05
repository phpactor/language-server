<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use LanguageServerProtocol\TextDocumentItem;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;

class ServerTesterTest extends TestCase
{
    public function testServerTester()
    {
        $builder = LanguageServerBuilder::create();
        $tester = $builder->buildServerTester();
        $responses = $tester->initialize();
        $this->assertCount(1, $responses);
    }

    public function testOpensDocument()
    {
        $builder = LanguageServerBuilder::create();
        $workspace = new Workspace();
        $builder->addSystemHandler(new TextDocumentHandler($workspace));
        $tester = $builder->buildServerTester();
        $tester->initialize();
        $responses = $tester->openDocument(new TextDocumentItem('file://foobar', 'some text'));
        $this->assertCount(1, $workspace);
    }
}
