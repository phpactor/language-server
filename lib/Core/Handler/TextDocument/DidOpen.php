<?php

namespace Phpactor\LanguageServer\Core\Handler\TextDocument;

use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;

class DidOpen implements Handler
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Manager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'textDocument/didOpen';
    }


    public function __invoke(TextDocumentItem $textDocument)
    {
        $this->sessionManager->current()->workspace()->open($textDocument);
    }
}
