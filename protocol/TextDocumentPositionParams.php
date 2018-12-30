<?php

namespace LanguageServerProtocol;

class TextDocumentPositionParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $textDocument;

    /**
     * @var Position
     */
    public $position;

    public function __construct(TextDocumentIdentifier $textDocument, Position $position)
    {
        $this->textDocument = $textDocument;
        $this->position = $position;
    }
}
