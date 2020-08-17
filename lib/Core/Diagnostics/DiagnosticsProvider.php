<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\call;

interface DiagnosticsProvider
{
    /**
     * @return Promise<array<Diagnostic>>
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise;
}
