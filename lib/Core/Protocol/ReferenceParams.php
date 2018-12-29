<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\Position;
use LanguageServerProtocol\ReferenceContext;
use LanguageServerProtocol\TextDocumentIdentifier;

class ReferenceParams extends TextDocumentPositionParams
{
    /**
     * @var ReferenceContext
     */
    public $context;

    public function __construct(TextDocumentIdentifier $textDocument, Position $position, ReferenceContext $context)
    {
        parent::__construct($textDocument, $position);
        $this->context = $context;
    }
}
