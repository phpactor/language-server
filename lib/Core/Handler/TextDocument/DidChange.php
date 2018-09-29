<?php

namespace Phpactor\LanguageServer\Core\Handler\TextDocument;

use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;

class DidChange implements Handler
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
        return 'textDocument/didChange';
    }


    public function __invoke(VersionedTextDocumentIdentifier $textDocument, array $contentChanges)
    {
        foreach ($contentChanges as $contentChange) {
            $contentChange = (array) $contentChange;

            $this->sessionManager->current()->workspace()->update($textDocument, $contentChange['text']);
        }
    }
}
