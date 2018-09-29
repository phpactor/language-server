<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\ExitServer;
use Phpactor\LanguageServer\Core\Handler\Shutdown;

class ShutdownTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return new Shutdown();
    }

    public function testResetsConnectionOnExit()
    {
        $this->expectException(ShutdownServer::class);
        $this->dispatch('shutdown', []);
    }
}
