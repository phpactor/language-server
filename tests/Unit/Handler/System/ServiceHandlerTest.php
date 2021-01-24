<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ServiceHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy<ServiceManager>
     */
    private $serviceManager;

    /**
     * @var ClientApi
     */
    private $api;

    /**
     * @var ServiceHandler
     */
    private $serviceHandler;

    /**
     * @var TestRpcClient
     */
    private $client;

    protected function setUp(): void
    {
        $this->serviceManager = $this->prophesize(ServiceManager::class);
        $this->client = TestRpcClient::create();
        $this->api = new ClientApi($this->client);
    }

    public function handler(): Handler
    {
        return new ServiceHandler(
            $this->serviceManager->reveal(),
            $this->api
        );
    }

    public function testItStartsAService(): void
    {
        $this->serviceManager->start('foobar')->shouldBeCalled();

        $this->dispatch('phpactor/service/start', [
            'name' => 'foobar'
        ]);
    }

    public function testItStopsAService(): void
    {
        $this->serviceManager->stop('foobar')->shouldBeCalled();
        $this->dispatch('phpactor/service/stop', [
            $this->serviceManager->reveal(),
            'name' => 'foobar'
        ]);
    }

    public function testReturnsRunningServices(): void
    {
        $this->serviceManager->runningServices()->willReturn([
            'one', 'two'
        ]);

        $this->dispatch('phpactor/service/running', [
            $this->serviceManager->reveal(),
            $this->api,
            'name' => 'foobar'
        ]);

        $message = $this->client->transmitter()->shift();

        self::assertInstanceOf(NotificationMessage::class, $message);
    }
}
