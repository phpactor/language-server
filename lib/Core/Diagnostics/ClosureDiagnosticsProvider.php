<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\Promise;
use Closure;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class ClosureDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        $closure = $this->closure;

        return $closure($textDocument);
    }
}
