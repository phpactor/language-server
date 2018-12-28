<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Generator;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;

interface Dispatcher
{
    public function dispatch(RequestMessage $request): Generator;
}
