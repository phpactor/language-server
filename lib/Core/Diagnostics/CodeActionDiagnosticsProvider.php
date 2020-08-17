<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Generator;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class CodeActionDiagnosticsProvider implements DiagnosticsProvider
{
    public function provideDiagnosticsFor(TextDocumentItem $textDocument): Generator
    {
    }
}
