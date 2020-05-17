<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface RpcClient
{
    public function notification(string $method, array $params): void;

    /**
     * @return Promise<ResponseMessage>
     */
    public function request(string $method, array $params): Promise;
}
