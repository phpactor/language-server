<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Generator;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Handler\Handlers;

interface Dispatcher
{
    public function dispatch(Handlers $handlers, RequestMessage $request): Generator;
}
