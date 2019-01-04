<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Handler\Handlers;

interface HandlerLoader
{
    public function load(InitializeParams $params): Handlers;
}
