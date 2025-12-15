<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;

class TextDocumentClosed
{
    public function __construct(private TextDocumentIdentifier $identifier)
    {
    }

    public function identifier(): TextDocumentIdentifier
    {
        return $this->identifier;
    }
}
