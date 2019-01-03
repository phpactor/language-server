<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use LanguageServerProtocol\InitializedParams;

class CallbackHandlerLoader implements HandlerLoader
{
    public function load(InitializedParams $params): HandlerRegistry
    {
        return new Handlers([]);
    }
}
