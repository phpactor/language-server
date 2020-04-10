<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface Dispatcher
{
    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Handlers $handlers, RequestMessage $request, array $extraArgs): Promise;
}
