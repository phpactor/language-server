<?php

namespace Phpactor\LanguageServer\Core\Diagnostics;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\call;

class AggregateDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @var array<DiagnosticsProvider>
     */
    private $providers;

    public function __construct(DiagnosticsProvider ...$providers)
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
                $diagnostics = array_merge(
                    $diagnostics,
                    yield $provider->provideDiagnostics($textDocument)
                );
            }

            return $diagnostics;
        });
    }
}
