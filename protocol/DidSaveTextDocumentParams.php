<?php

namespace LanguageServerProtocol;

class DidSaveTextDocumentParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $textDocument;

    /**
     * @var string|null
     */
    public $text;

    public function __construct(TextDocumentIdentifier $textDocument, ?string $text = null)
    {
        $this->textDocument = $textDocument;
        $this->text = $text;
    }
}
