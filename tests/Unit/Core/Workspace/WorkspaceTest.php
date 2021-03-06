<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Workspace;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Workspace\Exception\UnknownDocument;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class WorkspaceTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = new Workspace();
    }

    public function testThrowsExceptionGetUnknown(): void
    {
        $this->expectException(UnknownDocument::class);
        $this->workspace->get('foobar');
    }

    public function testOpensDocument(): void
    {
        $expectedDocument = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $this->workspace->open($expectedDocument);
        $document = $this->workspace->get('foobar');

        $this->assertSame($expectedDocument, $document);
    }

    public function testThrowsExceptionUpdateUnknown(): void
    {
        $this->expectException(UnknownDocument::class);

        $expectedDocument = new VersionedTextDocumentIdentifier('foobar');
        $this->workspace->update($expectedDocument, 'foobar');
    }

    public function testUpdatesDocument(): void
    {
        $originalDocument = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $expectedDocument = new VersionedTextDocumentIdentifier($originalDocument->uri);
        $this->workspace->open($originalDocument);
        $this->workspace->update($expectedDocument, 'my new text');
        $document = $this->workspace->get('foobar');

        $this->assertEquals($expectedDocument->uri, $document->uri);
        $this->assertEquals('my new text', $document->text);
    }

    public function testUpdatesDocumentVersion(): void
    {
        $originalDocument = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $expectedDocument = new VersionedTextDocumentIdentifier($originalDocument->uri, 5);
        $this->workspace->open($originalDocument);
        $this->workspace->update($expectedDocument, 'my new text');
        $document = $this->workspace->get('foobar');

        $this->assertEquals(5, $document->version);
    }

    /**
     * @dataProvider provideDoesNotUpdateDocumentWithLowerVersionThanExistingDocument
     */
    public function testDoesNotUpdateDocumentWithLowerVersionThanExistingDocument(int $originalVersion, ?int $newVersion, bool $shouldBeNewer): void
    {
        $originalDocument = new TextDocumentItem('foobar', 'php', $originalVersion, 'original document');

        $this->workspace->open($originalDocument);

        $oldDocument = new VersionedTextDocumentIdentifier($originalDocument->uri, $newVersion);

        $this->workspace->update($oldDocument, 'new document');

        $document = $this->workspace->get('foobar');

        $this->assertEquals($oldDocument->uri, $document->uri);
        $this->assertEquals($shouldBeNewer ? 'new document' : 'original document', $document->text);
    }

    public function provideDoesNotUpdateDocumentWithLowerVersionThanExistingDocument()
    {
        yield 'older document does not overwrite' => [
            5,
            4,
            false
        ];

        yield 'same versioned document overwrites' => [
            5,
            5,
            true
        ];

        yield 'null overwrites the document' => [
            5,
            null,
            true
        ];
    }

    public function testReturnsNumberOfOpenFiles(): void
    {
        $originalDocument = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $originalDocument->uri = 'foobar';
        $this->workspace->open($originalDocument);
        $this->assertEquals(1, $this->workspace->openFiles());
        $this->assertCount(1, $this->workspace);
    }

    public function testRemoveDocument(): void
    {
        $originalDocument = new TextDocumentItem('foobar', 'php', 1, 'foo');
        $originalDocument->uri = 'foobar';

        $this->workspace->open($originalDocument);
        $this->assertCount(1, $this->workspace);

        $identifier = new TextDocumentIdentifier('foobar');
        $this->workspace->remove($identifier);

        $this->assertCount(0, $this->workspace);
    }

    public function testIteratesOverDocuments(): void
    {
        $doc1 = new TextDocumentItem('foobar1', 'php', 1, 'foo');
        $doc2 = new TextDocumentItem('foobar2', 'php', 1, 'foo');

        $this->workspace->open($doc1);
        $this->workspace->open($doc2);

        $this->assertCount(2, iterator_to_array($this->workspace));
    }
}
