<?php

namespace Phpactor\LanguageServer\Core;

use Generator;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;

interface Dispatcher
{
    public function dispatch(Handlers $handlers, RequestMessage $request): Generator;
}
