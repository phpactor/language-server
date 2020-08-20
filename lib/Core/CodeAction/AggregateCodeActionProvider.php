<?php

namespace Phpactor\LanguageServer\Core\CodeAction;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\call;

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
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($textDocument, $range) {
            $actions = [];
            foreach ($this->providers as $provider) {
                $actions = array_merge($actions, yield $provider->provideActionsFor($textDocument, $range));
            }

            return $actions;
        });
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
