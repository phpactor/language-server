<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Handler\System\StatsHandler;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;

class StatsHandlerTest extends HandlerTestCase
{
    private ServerStats $stats;

    private ClientApi $clientApi;

    private TestRpcClient $client;

    protected function setUp(): void
    {
        $this->stats = new ServerStats();
        $this->client = TestRpcClient::create();
        $this->clientApi = new ClientApi($this->client);
    }

    public function handler(): Handler
    {
        return new StatsHandler($this->clientApi, $this->stats);
    }

    public function testItReturnsTheCurrentSessionStatus(): void
    {
        $tester = LanguageServerTesterBuilder::create()->addHandler($this->handler())->build();

        $response = $tester->requestAndWait('phpactor/stats', []);

        self::assertInstanceOf(ResponseMessage::class, $response);

        $message = $this->client->transmitter()->shift();

        self::assertNotNull($message);
    }
}
