<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Amp\Promise;
use DTL\Invoke\Invoke;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Server\RpcClient;

final class MessageRequestClient
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return Promise<MessageActionItem|null>
     */
    public function error(string $message, MessageActionItem ...$actions): Promise
    {
        return $this->requestMessage(MessageType::ERROR, $message, ...$actions);
    }

    /**
     * @return Promise<MessageActionItem|null>
     */
    public function warning(string $message, MessageActionItem ...$actions): Promise
    {
        return $this->requestMessage(MessageType::WARNING, $message, ...$actions);
    }

    /**
     * @return Promise<MessageActionItem|null>
     */
    public function info(string $message, MessageActionItem ...$actions): Promise
    {
        return $this->requestMessage(MessageType::INFO, $message, ...$actions);
    }

    /**
     * @return Promise<MessageActionItem|null>
     */
    public function log(string $message, MessageActionItem ...$actions): Promise
    {
        return $this->requestMessage(MessageType::LOG, $message, ...$actions);
    }

    /**
     * @return Promise<MessageActionItem|null>
     */
    private function requestMessage(int $messageType, string $message, MessageActionItem ...$actions): Promise
    {
        return \Amp\call(function () use ($messageType, $message, $actions) {
            $response = yield $this->client->request('window/showMessageRequest', [
                'type' => $messageType,
                'message' => $message,
                'actions' => $actions
            ]);

            $result = $response->result;

            if (null !== $result) {
                return Invoke::new(MessageActionItem::class, (array)$result);
            }

            return null;
        });
    }
}
