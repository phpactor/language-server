<?php

namespace Phpactor\LanguageServer\Core\Event;

use LanguageServerProtocol\TextDocumentIdentifier;
use League\Event\Event;

class DocumentRemoved extends Event
{
    const NAME = 'document_removed';

    /**
     * @var TextDocumentIdentifier
     */
    private $identifier;

    public function __construct(TextDocumentIdentifier $textDocument)
    {
        parent::__construct(self::NAME);

        $this->identifier = $textDocument;
    }

    public function identifier(): TextDocumentIdentifier
    {
        return $this->identifier;
    }
}
