<?php

namespace Phpactor\LanguageServer\Core\Handler\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;

class WillSaveWaitUntil implements Handler
{
    public function name(): string
    {
        return 'textDocument/willSaveWaitUntil';
    }

    public function __invoke(TextDocumentIdentifier $identifier, int $reason)
    {
    }
}
