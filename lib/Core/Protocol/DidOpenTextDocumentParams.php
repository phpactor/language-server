<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;

class DidOpenTextDocumentParams
{
    /**
     * @var TextDocumentItem
     */
    public $textDocument;

    public function __construct(TextDocumentItem $textDocument)
    {
        $this->textDocument = $textDocument;
    }
}
