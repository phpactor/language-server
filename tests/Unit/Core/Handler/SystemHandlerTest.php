<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Handler\SystemHandler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class SystemHandlerTest extends HandlerTestCase
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function setUp()
    {
        $this->sessionManager = $this->sessionManager();
        $this->sessionManager->initialize(__DIR__);
    }

    public function handler(): Handler
    {
        return new SystemHandler($this->sessionManager);
    }

    public function testItReturnsTheCurrentSessionStatus()
    {
        $responses = $this->dispatch('system/status', []);
        $this->assertInstanceOf(ResponseMessage::class, $responses[0], 'Returns dummy response to request');
        $this->assertInstanceOf(NotificationMessage::class, $responses[1], 'Issues notification with status');
    }
}
