<?php

namespace Phpactor\LanguageServer\Extension\Core\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;

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
