<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use DateInterval;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ServerClient;
use Phpactor\LanguageServer\Core\Server\ServerClient\MessageServerClient;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\StatProvider;
use Phpactor\LanguageServer\Test\HandlerTester;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Prophecy\Argument;

class ServiceHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $serviceManager;

    /**
     * @var ObjectProphecy
     */
    private $serverClient;

    /**
     * @var ServiceHandler
     */
    private $serviceHandler;

    protected function setUp(): void
    {
        $this->serviceManager = $this->prophesize(ServiceManager::class);
        $this->serverClient = $this->prophesize(ServerClient::class);
        $this->serviceHandler = new ServiceHandler();
    }

    public function handler(): Handler
    {
        return new ServiceHandler();
    }

    public function testItStartsAService()
    {
        $this->serviceManager->start('foobar')->shouldBeCalled();
        $this->dispatch('service/start', [
            $this->serviceManager->reveal(),
            'name' => 'foobar'
        ]);
    }

    public function testItStopsAService()
    {
        $this->serviceManager->stop('foobar')->shouldBeCalled();
        $this->dispatch('service/stop', [
            $this->serviceManager->reveal(),
            'name' => 'foobar'
        ]);
    }

    public function testReturnsRunningServices()
    {
        $this->serviceManager->runningServices()->willReturn([
            'one', 'two'
        ]);
        $this->serverClient->notification('window/showMessage', Argument::cetera())->shouldBeCalled();

        $this->dispatch('service/running', [
            $this->serviceManager->reveal(),
            $this->serverClient->reveal(),
            'name' => 'foobar'
        ]);
    }
}
