<?php

namespace Phpactor\LanguageServer\Core\Server\RpcClient;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;

class TestRpcClient implements RpcClient
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
     * @var ResponseWatcher
     */
    private $responseWatcher;

    private function __construct(TestMessageTransmitter $transmitter, ResponseWatcher $responseWatcher)
    {
        $this->transmitter = $transmitter;
        $this->responseWatcher = $responseWatcher;
        $this->client = new JsonRpcClient($transmitter, $responseWatcher);
    }

    public static function create(): TestRpcClient
    {
        return new self(new TestMessageTransmitter(), new ResponseWatcher());
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

    public function responseWatcher(): ResponseWatcher
    {
        return $this->responseWatcher;
    }

    public function transmitter(): TestMessageTransmitter
    {
        return $this->transmitter;
    }
}
