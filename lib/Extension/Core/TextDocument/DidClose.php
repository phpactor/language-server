<?php

namespace Phpactor\LanguageServer\Extension\Core\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;

class DidClose implements Handler
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
        return 'textDocument/didClose';
    }


    public function __invoke(TextDocumentIdentifier $textDocument)
    {
        $this->sessionManager->current()->workspace()->remove($textDocument);
    }
}
