<?php

namespace Phpactor\LanguageServer\Diagnostics;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class CodeActionDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var array<CodeActionProvider>
     */
    private $providers;

    public function __construct(CodeActionProvider ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = [];
            foreach ($this->providers as $provider) {
                $codeActions = yield $provider->provideActionsFor($textDocument, new Range(
                    new Position(0, 0),
                    new Position(PHP_INT_MAX, PHP_INT_MAX)
                ));

                foreach ($codeActions as $codeAction) {
                    foreach ($codeAction->diagnostics as $diagnostic) {
                        $diagnostics[] = $diagnostic;
                    }
                }
            }

            return $diagnostics;
        });
    }
}
