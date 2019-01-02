<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Handler\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Session\Session;
use Phpactor\LanguageServer\Core\Session\SessionManager;

class TextDocumentHandlerTest extends HandlerTestCase
{
    /**
     * @var SessionManager
     */
    private $manager;

    /**
     * @var EventEmitter
     */
    private $emitter;

    public function setUp()
    {
        $this->manager = $this->sessionManager();
        $this->emitter = $this->emitter();
        $this->manager->load(new Session('path/to'));
    }

    public function handler(): Handler
    {
        return new TextDocumentHandler(
            $this->emitter,
            $this->manager
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
            $this->manager->current()->workspace()->get($textDocument->uri),
            $textDocument
        );
    }

    public function testUpdatesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $this->manager->current()->workspace()->open($document);
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
        $this->manager->current()->workspace()->open($document);
        $this->dispatch('textDocument/didClose', [
            'textDocument' => new TextDocumentIdentifier('foobar'),
            'contentChanges' => [
                [
                    'text' => 'asd',
                ]
            ],
        ]);

        $this->assertFalse($this->manager->current()->workspace()->has('foobar'));
    }

    public function testSavesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $workspace = $this->manager->current()->workspace();
        $workspace->open($document);
        $this->dispatch('textDocument/didSave', [
            'textDocument' => new TextDocumentIdentifier('foobar'),
            'text' => 'hello',
        ]);

        $this->assertEquals('hello', $workspace->get('foobar')->text);
    }
}
