<?php

namespace Phpactor\LanguageServer\Core\Server\ResponseWatcher;

use Amp\Deferred;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use RuntimeException;

final class DeferredResponseWatcher implements ResponseWatcher
{
    /**
     * @var array<string|int, Deferred<ResponseMessage>>
     */
    private $watchers = [];

    public function handle(ResponseMessage $response): void
    {
        if (isset($this->watchers[$response->id])) {
            $this->watchers[$response->id]->resolve($response);
            return;
        }

        throw new RuntimeException(sprintf(
            'Response to unknown request "%s"',
            $response->id
        ));
    }

    /**
     * @param string|int $requestId
     *
     * @return Promise<ResponseMessage>
     */
    public function waitForResponse($requestId): Promise
    {
        $deferred = new Deferred();
        $this->watchers[$requestId] = $deferred;
        return $deferred->promise();
    }
}
