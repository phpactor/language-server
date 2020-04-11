<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

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
        $requestId = uniqid();
        $response = $this->responseWatcher->waitForResponse($requestId);
        $this->transmitter->transmit(new NotificationMessage($method, $params));

        return $response->wait();
    }
}
