<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;

class TextDocumentSaved
{
    public function __construct(private TextDocumentIdentifier $identifier, private ?string $text = null)
    {
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
