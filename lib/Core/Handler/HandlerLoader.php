<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;

interface HandlerLoader
{
    public function load(InitializeParams $params): Handlers;
}
