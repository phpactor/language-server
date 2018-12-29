<?php

namespace LanguageServerProtocol;

class CodeLensParams
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
