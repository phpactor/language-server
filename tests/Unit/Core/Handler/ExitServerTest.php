<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\ExitServer;

class ExitServerTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return new ExitServer();
    }

    public function testIssuesShutdown()
    {
        $this->expectException(ShutdownServer::class);
        foreach ($this->dispatch('exit', []) as $message) {
        }
    }
}
