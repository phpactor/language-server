<?php

namespace Phpactor\LanguageServer\Core;

use Generator;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

interface Dispatcher
{
    public function dispatch(RequestMessage $request): Generator;
}
