<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use LanguageServerProtocol\InitializedParams;

interface HandlerLoader
{
    public function load(InitializedParams $params): HandlerRegistry;
}
