<?php

namespace Phpactor\LanguageServer\Core\CodeAction;

use Generator;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

interface CodeActionProvider
{
    /**
     * @return Generator<CodeAction>
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Generator;

    /**
     * Return the kinds of actions that this provider can return, for example
     * "refactor.extract", "quickfix", etc.
     *
     * @see Phpactor\LanguageServerProtocol\CodeAction
     *
     * @return array<string>
     */
    public function kinds(): array;
}
