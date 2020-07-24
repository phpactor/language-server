<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface Dispatcher
{
    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $request): Promise;
}
