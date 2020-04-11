<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Ramsey\Uuid\Nonstandard\UuidBuilder;
use Ramsey\Uuid\Uuid;

class ServerClient
{
    /**
     * @var MessageTransmitter
     */
    private $transmitter;

    /**
     * @var ResponseWatcher
     */
    private $responseWatcher;


    public function __construct(MessageTransmitter $transmitter, ResponseWatcher $responseWatcher)
    {
        $this->transmitter = $transmitter;
        $this->responseWatcher = $responseWatcher;
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
        $requestId = Uuid::uuid4();
        $response = $this->responseWatcher->waitForResponse((string)$requestId);
        $this->transmitter->transmit(new RequestMessage($requestId, $method, $params));

        return $response;
    }
}
