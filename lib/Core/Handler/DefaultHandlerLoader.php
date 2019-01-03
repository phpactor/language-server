<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\Evenement\EvenementEmitter;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerLoader;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry\Handlers;

class DefaultHandlerLoader implements HandlerLoader
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
