<?php

namespace Phpactor\LanguageServer\Extension\Core\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;

class DidSave implements Handler
{
    public function name(): string
    {
        return 'textDocument/didSave';
    }

    public function __invoke(TextDocumentIdentifier $textDocument, ?string $text = null)
    {
    }
}
