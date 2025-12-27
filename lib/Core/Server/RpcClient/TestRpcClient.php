<?php

namespace Phpactor\LanguageServer\Core\Server\RpcClient;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;

final class TestRpcClient implements RpcClient
{
    private JsonRpcClient $client;

    public function __construct(private TestMessageTransmitter $transmitter, private TestResponseWatcher $responseWatcher)
    {
        $this->client = new JsonRpcClient($this->transmitter, $this->responseWatcher);
    }

    public static function create(): TestRpcClient
    {
        return new self(new TestMessageTransmitter(), new TestResponseWatcher());
    }

    public function notification(string $method, array $params): void
    {
        $this->client->notification($method, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $method, array $params): Promise
    {
        return $this->client->request($method, $params);
    }

    public function responseWatcher(): TestResponseWatcher
    {
        return $this->responseWatcher;
    }

    public function transmitter(): TestMessageTransmitter
    {
        return $this->transmitter;
    }
}
