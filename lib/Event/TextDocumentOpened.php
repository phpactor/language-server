<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentItem;

class TextDocumentOpened
{
    /**
     * @var TextDocumentItem
     */
    private $textDocument;

    public function __construct(TextDocumentItem $textDocument)
    {
        $this->textDocument = $textDocument;
    }

    public function textDocument(): TextDocumentItem
    {
        return $this->textDocument;
    }
}
