<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

interface ResponseWatcher
{
    public function handle(ResponseMessage $response): void;

    /**
     * @return Promise<ResponseMessage>
     */
    public function waitForResponse(int|string $requestId): Promise;
}
