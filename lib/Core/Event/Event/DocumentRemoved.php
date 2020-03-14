<?php

namespace Phpactor\LanguageServer\Core\Event;

use LanguageServerProtocol\IdentifierItem;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\VersionedIdentifierIdentifier;
use League\Event\EmitterInterface;
use League\Event\Event;
use League\Event\EventInterface;

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
