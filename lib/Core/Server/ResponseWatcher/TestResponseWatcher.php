<?php

namespace Phpactor\LanguageServer\Core\Server\ResponseWatcher;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use RuntimeException;

final class TestResponseWatcher implements ResponseWatcher
{
    /**
     * @var ResponseWatcher
     */
    private $innerWatcher;

    /**
     * @var array
     */
    private $requestIds = [];

    /**
     * @var ResponseMessage|null
     */
    private $nextResponse = null;

    public function __construct(?ResponseWatcher $innerWatcher = null)
    {
        $this->innerWatcher = $innerWatcher ?: new DeferredResponseWatcher();
    }

    public function handle(ResponseMessage $response): void
    {
        $this->innerWatcher->handle($response);
    }

    /**
     * @param mixed $result
     */
    public function resolveNextResponse($result, ?ResponseError $error = null): void
    {
        $this->nextResponse = new ResponseMessage(0, $result, $error);
    }

    /**
     * @param mixed $result
     */
    public function resolveLastResponse($result): void
    {
        $id = array_shift($this->requestIds);
        if (null === $id) {
            throw new RuntimeException(
                'No responses left to handle'
            );
        }
        $this->handle(new ResponseMessage($id, $result));
    }

    /**
     * {@inheritDoc}
     */
    public function waitForResponse($requestId): Promise
    {
        $this->requestIds[] = $requestId;
        $promise = $this->innerWatcher->waitForResponse($requestId);

        if ($this->nextResponse) {
            $this->handle(new ResponseMessage(
                $requestId,
                $this->nextResponse->result,
                $this->nextResponse->error,
            ));
        }

        return $promise;
    }
}
