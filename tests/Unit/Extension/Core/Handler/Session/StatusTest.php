<?php

namespace Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler\Session;

use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Extension\Core\Session\Status;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use Phpactor\LanguageServer\Tests\Unit\Extension\Core\Handler\HandlerTestCase;

class StatusTest extends HandlerTestCase
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function setUp()
    {
        $this->sessionManager = new Manager();
        $this->sessionManager->initialize(__DIR__);
    }

    public function handler(): Handler
    {
        return new Status($this->sessionManager);
    }

    public function testItReturnsTheCurrentSessionStatus()
    {
        $responses = $this->dispatch('session/status', []);
        $this->assertInstanceOf(ResponseMessage::class, $responses[0], 'Returns dummy response to request');
        $this->assertInstanceOf(NotificationMessage::class, $responses[1], 'Issues notification with status');
    }
}