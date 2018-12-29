<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentIdentifier;

class RenameParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $textDocument;

    /**
     * @var Position
     */
    public $position;

    /**
     * @var string
     */
    public $newName;

    public function __construct(TextDocumentIdentifier $textDocument, Position $position, string $newName)
    {
        $this->textDocument = $textDocument;
        $this->position = $position;
        $this->newName = $newName;
    }
}
