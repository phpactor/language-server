<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler\TextDocument;

use LanguageServerProtocol\ContentChangeEvent;
use LanguageServerProtocol\TextDocumentContentChangeEvent;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\TextDocument\DidChange;
use Phpactor\LanguageServer\Core\Handler\TextDocument\DidOpen;
use Phpactor\LanguageServer\Tests\Unit\Core\Handler\HandlerTestCase;
use phpDocumentor\Reflection\DocBlock\Tags\Version;

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
