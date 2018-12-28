<?php

namespace Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler\TextDocument;

use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidChange;
use Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler\HandlerTestCase;

class DidChangeTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return new DidChange($this->session());
    }

    public function testUpdatesDocument()
    {
        $document = new TextDocumentItem();
        $document->uri = 'foobar';
        $this->session()->current()->workspace()->open($document);
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
}
