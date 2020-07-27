<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface MethodRunner
{
    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $request): Promise;

    /**
     * @param int|string $id
     */
    public function cancelRequest($id): void;
}
