<?php

namespace Phpactor\LanguageServer\Core\Event;

use LanguageServerProtocol\TextDocumentItem;
use League\Event\Event;

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
