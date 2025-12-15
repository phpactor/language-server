<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\CancellationToken;
use Amp\Promise;
use Closure;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class ClosureDiagnosticsProvider implements DiagnosticsProvider
{
    public function __construct(private Closure $closure, private string $name = 'closure')
    {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        $closure = $this->closure;

        return $closure($textDocument);
    }

    public function name(): string
    {
        return $this->name;
    }
}
