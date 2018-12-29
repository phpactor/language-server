<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentIdentifier;

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
