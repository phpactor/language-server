<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

interface DiagnosticsProvider
{
    /**
     * @return Promise<array<Diagnostic>>
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise;
}
