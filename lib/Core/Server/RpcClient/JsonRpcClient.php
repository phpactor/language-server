<?php

namespace Phpactor\LanguageServer\Core\Server\RpcClient;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Ramsey\Uuid\Uuid;

final class JsonRpcClient implements RpcClient
{
    public function __construct(private MessageTransmitter $transmitter, private ResponseWatcher $responseWatcher)
    {
    }

    public function notification(string $method, array $params): void
    {
        $this->transmitter->transmit(new NotificationMessage($method, $params));
    }

    /**
     * @return Promise<ResponseMessage>
     */
    public function request(string $method, array $params): Promise
    {
        $requestId = Uuid::uuid4()->__toString();
        $response = $this->responseWatcher->waitForResponse((string)$requestId);
        $this->transmitter->transmit(new RequestMessage($requestId, $method, $params));

        return $response;
    }
}
