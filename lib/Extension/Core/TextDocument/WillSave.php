<?php

namespace Phpactor\LanguageServer\Extension\Core\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;

class WillSave implements Handler
{
    public function name(): string
    {
        return 'textDocument/willSave';
    }

    public function __invoke(TextDocumentIdentifier $identifier, int $reason)
    {
    }
}
