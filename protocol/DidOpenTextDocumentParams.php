<?php

namespace LanguageServerProtocol;

class DidOpenTextDocumentParams
{
    /**
     * @var TextDocumentItem
     */
    public $textDocument;

    public function __construct(TextDocumentItem $textDocument)
    {
        $this->textDocument = $textDocument;
    }
}
