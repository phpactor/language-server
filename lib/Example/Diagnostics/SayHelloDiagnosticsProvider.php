<?php

namespace Phpactor\LanguageServer\Example\Diagnostics;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class SayHelloDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        /** @phpstan-ignore-next-line */
        return call(function () {
            return [
                new Diagnostic(
                    new Range(
                        new Position(0, 0),
                        new Position(1, 0)
                    ),
                    'This is the first line, hello!',
                    DiagnosticSeverity::INFORMATION
                )
            ];
        });
    }

    public function name(): string
    {
        return 'say-hello';
    }
}
