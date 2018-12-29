<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\CompletionContext;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentIdentifier;

class CompletionParams extends TextDocumentPositionParams
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
     * @var CompletionContext
     */
    public $context;

    public function __construct(TextDocumentIdentifier $textDocument, Position $position, CompletionContext $context)
    {
        parent::__construct($textDocument, $position);
        $this->context = $context;
    }
}
