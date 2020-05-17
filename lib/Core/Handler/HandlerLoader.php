<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Server\SessionServices;

interface HandlerLoader
{
    public function load(InitializeParams $params, SessionServices $services): Handlers;
}
