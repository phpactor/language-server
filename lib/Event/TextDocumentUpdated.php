<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;

class TextDocumentUpdated
{
    /**
     * @var VersionedTextDocumentIdentifier
     */
    private $identifier;
    /**
     * @var string
     */
    private $updatedText;

    public function __construct(VersionedTextDocumentIdentifier $identifier, string $updatedText)
    {
        $this->identifier = $identifier;
        $this->updatedText = $updatedText;
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
