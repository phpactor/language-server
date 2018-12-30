<?php

namespace LanguageServerProtocol;

class DidCloseTextDocumentParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $textDocument;

    public function __construct(TextDocumentIdentifier $textDocument)
    {
        $this->textDocument = $textDocument;
    }
}
