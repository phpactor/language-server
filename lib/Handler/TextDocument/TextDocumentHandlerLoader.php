<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Core\Handler\HandlerLoader;
use Phpactor\LanguageServer\Core\Handler\Handlers;

class TextDocumentHandlerLoader implements HandlerLoader
{
    public function load(InitializeParams $params): Handlers
    {
        $workspace = new Workspace();

        return new Handlers([
            new TextDocumentHandler(
                $workspace
            )
        ]);
    }
}
