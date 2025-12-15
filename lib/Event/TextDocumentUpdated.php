<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;

class TextDocumentUpdated
{
    public function __construct(private VersionedTextDocumentIdentifier $identifier, private string $updatedText)
    {
    }

    public function identifier(): VersionedTextDocumentIdentifier
    {
        return $this->identifier;
    }

    public function updatedText(): string
    {
        return $this->updatedText;
    }
}
