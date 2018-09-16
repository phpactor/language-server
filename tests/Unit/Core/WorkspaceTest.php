<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use LanguageServerProtocol\TextDocumentItem;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\UnknownDocument;
use Phpactor\LanguageServer\Core\Workspace;

class WorkspaceTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp()
    {
        $this->workspace = new Workspace();
    }

    public function testThrowsExceptionGetUnknown()
    {
        $this->expectException(UnknownDocument::class);
        $this->workspace->get('foobar');
    }

    public function testOpensDocument()
    {
        $expectedDocument = new TextDocumentItem();
        $expectedDocument->uri = 'foobar';
        $this->workspace->open($expectedDocument);
        $document = $this->workspace->get('foobar');

        $this->assertSame($expectedDocument, $document);
    }

    public function testThrowsExceptionUpdateUnknown()
    {
        $this->expectException(UnknownDocument::class);

        $expectedDocument = new TextDocumentItem();
        $expectedDocument->uri = 'foobar';
        $this->workspace->update($expectedDocument, 'foobar');
    }

    public function testUpdatesDocument()
    {
        $expectedDocument = new TextDocumentItem();
        $expectedDocument->uri = 'foobar';
        $this->workspace->open($expectedDocument);
        $this->workspace->update($expectedDocument, 'my new text');
        $document = $this->workspace->get('foobar');

        $this->assertSame($expectedDocument, $document);
        $this->assertEquals('my new text', $document->text);
    }
}
