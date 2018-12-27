<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Session;

use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Session\Exception\UnknownDocument;
use Phpactor\LanguageServer\Core\Session\Workspace;

class WorkspaceTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp()
    {
        $this->workspace = new Workspace('/path/to');
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

        $expectedDocument = new VersionedTextDocumentIdentifier();
        $expectedDocument->uri = 'foobar';
        $this->workspace->update($expectedDocument, 'foobar');
    }

    public function testUpdatesDocument()
    {
        $originalDocument = new TextDocumentItem();
        $originalDocument->uri = 'foobar';
        $expectedDocument = new VersionedTextDocumentIdentifier();
        $expectedDocument->uri = $originalDocument->uri;
        $this->workspace->open($originalDocument);
        $this->workspace->update($expectedDocument, 'my new text');
        $document = $this->workspace->get('foobar');

        $this->assertEquals($expectedDocument->uri, $document->uri);
        $this->assertEquals('my new text', $document->text);
    }
}
