<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\CancellationToken;
use Amp\Promise;
use Closure;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class ClosureDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var Closure
     */
    private $closure;

    private string $name;

    public function __construct(Closure $closure, string $name = 'closure')
    {
        $this->closure = $closure;
        $this->name = $name;
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
