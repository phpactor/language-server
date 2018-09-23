<?php

namespace Phpactor\LanguageServer\Example;

use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\SessionManager;

class ExampleCompletionHandler implements Handler
{
    private $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'textDocument/completion';
    }

    public function __invoke(TextDocumentItem $textDocument, Position $position): CompletionList
    {
        $textDocument = $this->sessionManager->current()->workspace()->get($textDocuemnt->uri);
        $completionList = new CompletionList();

        // ... do whatever we need to do to get the completion information

        $completionList->items[] = new CompletionItem('foobar');
        $completionList->items[] = new CompletionItem('foofoo');

        return $completionList;
    }
}
