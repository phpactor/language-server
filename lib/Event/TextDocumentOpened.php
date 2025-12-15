<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentItem;

class TextDocumentOpened
{
    public function __construct(private TextDocumentItem $textDocument)
    {
    }

    public function textDocument(): TextDocumentItem
    {
        return $this->textDocument;
    }
}
