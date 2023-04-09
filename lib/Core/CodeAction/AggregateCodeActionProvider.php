<?php

namespace Phpactor\LanguageServer\Core\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\call;
use function Amp\delay;

class AggregateCodeActionProvider implements CodeActionProvider
{
    /**
     * @var CodeActionProvider[]
     */
    private array $providers;

    public function __construct(CodeActionProvider ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            $actions = [];
            foreach ($this->providers as $provider) {
                $actions = array_merge($actions, yield $provider->provideActionsFor($textDocument, $range, $cancel));

                yield delay(0);

                $cancel->throwIfRequested();
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

    public function describe(): string
    {
        return sprintf('aggregate code action proivder with %s providers', count($this->providers));
    }
}
