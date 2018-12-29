<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\CodeActionContext;
use LanguageServerProtocol\Range;
use LanguageServerProtocol\TextDocumentIdentifier;

class CodeActionParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $textDocument;

    /**
     * @var Range
     */
    public $range;

    /**
     * @var CodeActionContext
     */
    public $context;

    public function __construct(TextDocumentIdentifier $textDocument, Range $range, CodeActionContext $context)
    {
        $this->textDocument = $textDocument;
        $this->range = $range;
        $this->context = $context;
    }
}
