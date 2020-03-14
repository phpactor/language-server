<?php

namespace Phpactor\LanguageServer\Core\Event;

use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use League\Event\EmitterInterface;
use League\Event\Event;
use League\Event\EventInterface;

class DocumentUpdated extends Event
{
    /**
     * @var VersionedTextDocumentIdentifier
     */
    private $textDocument;

    /**
     * @var string
     */
    private $updatedText;

    public function __construct(VersionedTextDocumentIdentifier $textDocument, string $updatedText)
    {
        parent::__construct('document_updated');

        $this->textDocument = $textDocument;
        $this->updatedText = $updatedText;
    }

    public function textDocument(): VersionedTextDocumentIdentifier
    {
        return $this->textDocument;
    }

    public function updatedText(): string
    {
        return $this->updatedText;
    }
}
