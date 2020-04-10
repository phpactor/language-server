<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;

class TextDocumentHandlerTest extends HandlerTestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = new Workspace();
    }

    public function handler(): Handler
    {
        return new TextDocumentHandler(
            $this->workspace
        );
    }

    public function testOpensDocument()
    {
        $textDocument = new TextDocumentItem();
        $textDocument->uri = 'foobar.html';

        $this->dispatch('textDocument/didOpen', [
            'textDocument' => $textDocument
        ]);

        $this->assertSame(
            $this->workspace->get($textDocument->uri),
            $textDocument
        );
    }

    public function testUpdatesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $this->workspace->open($document);
        $this->dispatch('textDocument/didChange', [
            'textDocument' => new VersionedTextDocumentIdentifier('foobar'),
            'contentChanges' => [
                [
                    'text' => 'asd',
                ]
            ],
        ]);

        $this->assertEquals('asd', $document->text);
    }

    public function testWillSave()
    {
        $response = $this->dispatch('textDocument/willSave', [
            'identifier' => new TextDocumentIdentifier('foobar'),
            'reason' => 1
        ]);
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertNull($response->result);
    }

    public function testClosesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $this->workspace->open($document);
        $this->dispatch('textDocument/didClose', [
            'textDocument' => new TextDocumentIdentifier('foobar'),
            'contentChanges' => [
                [
                    'text' => 'asd',
                ]
            ],
        ]);

        $this->assertFalse($this->workspace->has('foobar'));
    }

    public function testSavesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $workspace = $this->workspace;
        $workspace->open($document);
        $this->dispatch('textDocument/didSave', [
            'textDocument' => new TextDocumentIdentifier('foobar'),
            'text' => 'hello',
        ]);

        $this->assertEquals('hello', $workspace->get('foobar')->text);
    }
}
