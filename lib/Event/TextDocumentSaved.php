<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;

class TextDocumentSaved
{
    private TextDocumentIdentifier $identifier;

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
