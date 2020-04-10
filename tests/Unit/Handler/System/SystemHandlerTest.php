<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use DateInterval;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\StatProvider;
use Phpactor\LanguageServer\Test\HandlerTester;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;

class SystemHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $provider;

    protected function setUp(): void
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
        $tester = new HandlerTester($this->handler());

        $response = $tester->dispatch('system/status', []);

        self::assertInstanceOf(ResponseMessage::class, $response);
        $message = $tester->transmitter()->shift();
        self::assertNotNull($message);
    }
}
