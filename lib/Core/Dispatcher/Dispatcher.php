<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Handler\Handlers;

interface Dispatcher
{
    /**
     * @return Promise<Message|null>
     */
    public function dispatch(Handlers $handlers, RequestMessage $request, array $extraArgs): Promise;
}
