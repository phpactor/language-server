<?php

namespace Phpactor\LanguageServer\Core\Event;

use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use League\Event\EmitterInterface;
use League\Event\Event;
use League\Event\EventInterface;

class DocumentOpened extends Event
{
    const NAME = 'document_opened';

    /**
     * @var TextDocumentItem
     */
    private $textDocument;

    public function __construct(TextDocumentItem $textDocument)
    {
        parent::__construct(self::NAME);

        $this->textDocument = $textDocument;
    }

    public function textDocument(): TextDocumentItem
    {
        return $this->textDocument;
    }
}
