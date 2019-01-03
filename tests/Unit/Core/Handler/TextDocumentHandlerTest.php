<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Handler\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Session\Workspace;

class TextDocumentHandlerTest extends HandlerTestCase
{
    /**
     * @var EventEmitter
     */
    private $emitter;

    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp()
    {
        $this->workspace = new Workspace();
        $this->emitter = $this->emitter();
    }

    public function handler(): Handler
    {
        return new TextDocumentHandler(
            $this->emitter,
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
        $called = false;
        $this->emitter->on(LanguageServerEvents::TEXT_DOCUMENT_WILL_SAVE, function () use (&$called) {
            $called = true;
        });
        $this->dispatch('textDocument/willSave', [
            'identifier' => new TextDocumentIdentifier('foobar'),
            'reason' => 1
        ]);

        $this->assertTrue($called);
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
