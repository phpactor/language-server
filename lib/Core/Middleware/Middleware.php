<?php

namespace Phpactor\LanguageServer\Core\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface Middleware
{
    /**
     * @return Promise<ResponseMessage|null>
     */
    public function process(Message $request, RequestHandler $handler): Promise;
}
