<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry\Handlers;

interface HandlerLoader
{
    public function load(InitializeParams $params): Handlers;
}
