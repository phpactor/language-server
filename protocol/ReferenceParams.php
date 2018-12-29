<?php

namespace LanguageServerProtocol;

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
