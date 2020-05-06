<?php

namespace Phpactor\LanguageServer\Event;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;

class TextDocumentSaved
{
    /**
     * @var TextDocumentIdentifier
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $text;

    public function __construct(TextDocumentIdentifier $identifier, ?string $text = null)
    {
        $this->identifier = $identifier;
        $this->text = $text;
    }

    public function identifier(): TextDocumentIdentifier
    {
        return $this->identifier;
    }

    public function text(): ?string
    {
        return $this->text;
    }
}
