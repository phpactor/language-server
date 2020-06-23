<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;

class TextDocumentClosed
{
    /**
     * @var TextDocumentIdentifier
     */
    private $identifier;

    public function __construct(TextDocumentIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public function identifier(): TextDocumentIdentifier
    {
        return $this->identifier;
    }
}
