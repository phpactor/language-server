<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use DateInterval;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Handler\SystemHandler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\StatProvider;

class SystemHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $provider;

    public function setUp()
    {
        $this->provider = $this->prophesize(StatProvider::class);
        $stats = new ServerStats(
            new DateInterval('PT1S'),
            5,
            6
        );
        $this->provider->stats()->willReturn($stats);
    }

    public function handler(): Handler
    {
        return new SystemHandler($this->provider->reveal());
    }

    public function testItReturnsTheCurrentSessionStatus()
    {
        $responses = $this->dispatch('system/status', []);
        $this->assertInstanceOf(ResponseMessage::class, $responses[0], 'Returns dummy response to request');
        $this->assertInstanceOf(NotificationMessage::class, $responses[1], 'Issues notification with status');
    }
}
