<?php

namespace Phpactor\LanguageServer\Example\CodeAction;

use Generator;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class SayHelloCodeActionProvider implements CodeActionProvider
{
    /**
     * {@inheritDoc}
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Generator
    {
        yield CodeAction::fromArray([
            'title' => 'Alice',
            'command' => new Command('Hello Alice', 'phpactor.say_hello', [
                'Alice',
            ])
        ]);

        yield CodeAction::fromArray([
            'title' => 'Bob',
            'command' => new Command('Hello Bob', 'phpactor.say_hello', [
                'Bob',
            ])
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return ['example'];
    }
}
