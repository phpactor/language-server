<?php

namespace Phpactor\LanguageServer\Core\Server\RpcClient;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;

final class TestRpcClient implements RpcClient
{
    /**
     * @var JsonRpcClient
     */
    private $client;

    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    /**
     * @var TestResponseWatcher
     */
    private $responseWatcher;

    public function __construct(TestMessageTransmitter $transmitter, TestResponseWatcher $responseWatcher)
    {
        $this->transmitter = $transmitter;
        $this->responseWatcher = $responseWatcher;
        $this->client = new JsonRpcClient($transmitter, $responseWatcher);
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
