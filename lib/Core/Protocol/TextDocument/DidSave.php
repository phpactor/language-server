<?php

namespace Phpactor\LanguageServer\Core\Protocol\TextDocument;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;

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
