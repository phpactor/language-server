<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Phpactor\LanguageServer\Core\Server\RpcClient;

final class WindowClient
{
    /**
     * @var RpcClient
     */
    private $client;

    public function __construct(RpcClient $client)
    {
        $this->client = $client;
    }

    public function showMessage(): MessageClient
    {
        return new MessageClient($this->client, 'window/showMessage');
    }

    public function logMessage(): MessageClient
    {
        return new MessageClient($this->client, 'window/logMessage');
    }

    public function showMessageRequest(): MessageRequestClient
    {
        return new MessageRequestClient($this->client);
    }
}
