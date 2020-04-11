<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Deferred;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;

class ResponseWatcher
{
    /**
     * @var array<string, Deferred<ResponseMessage>>
     */
    private $watchers = [];

    public function handle(ResponseMessage $response): void
    {
        if (isset($this->watchers[$response->id])) {
            $this->watchers[$response->id]->resolve($response);
            return;
        }

        throw new RuntimeException(sprintf(
            'Response to unknown request "%s"', $response->id
        ));
    }

    /**
     * @return Promise<ResponseMessage>
     */
    public function waitForResponse(string $requestId): Promise
    {
        $deferred = new Deferred();
        $this->watchers[$requestId] = $deferred;
        return $deferred->promise();
    }
}
