<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Test\HandlerTester;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;

class SystemHandlerTest extends HandlerTestCase
{
    /**
     * @var ServerStats
     */
    private $stats;

    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var TestRpcClient
     */
    private $client;

    protected function setUp(): void
    {
        $this->stats = new ServerStats();
        $this->client = TestRpcClient::create();
        $this->clientApi = new ClientApi($this->client);
    }

    public function handler(): Handler
    {
        return new SystemHandler($this->clientApi, $this->stats);
    }

    public function testItReturnsTheCurrentSessionStatus()
    {
        $tester = new HandlerTester($this->handler());

        $response = $tester->dispatchAndWait('system/status', []);
        self::assertInstanceOf(ResponseMessage::class, $response);


        $message = $this->client->transmitter()->shift();

        self::assertNotNull($message);
    }
}
