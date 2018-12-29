<?php

namespace LanguageServerProtocol;

class DidChangeTextDocumentParams
{
    /**
     * @var VersionedTextDocumentIdentifier
     */
    public $textDocument;

    /**
     * @var array
     */
    public $contentChanges;

    public function __construct(VersionedTextDocumentIdentifier $textDocument, array $contentChanges)
    {
        $this->textDocument = $textDocument;
        $this->contentChanges = $contentChanges;
    }
}
