<?php

namespace Phpactor\LanguageServer\Core\CodeAction;

use Generator;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class AggregateCodeActionProvider implements CodeActionProvider
{
    /**
     * @var array
     */
    private $providers;

    public function __construct(CodeActionProvider ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Generator
    {
        foreach ($this->providers as $provider) {
            yield from $provider->provideActionsFor($textDocument, $range);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return array_values(
            array_reduce(
                $this->providers,
                function (array $kinds, CodeActionProvider $provider) {
                    return array_merge(
                        $kinds,
                        (array)array_combine($provider->kinds(), $provider->kinds())
                    );
                },
                []
            )
        );
    }
}
