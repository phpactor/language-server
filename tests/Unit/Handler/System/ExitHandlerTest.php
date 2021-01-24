<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;

class ExitHandlerTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return new ExitHandler();
    }

    public function testThrowsExitSessionException(): void
    {
        $this->expectException(ExitSession::class);
        $this->dispatch('exit', []);
    }

    public function testShutdownDoesNothing(): void
    {
        $this->dispatch('shutdown', []);
        $this->addToAssertionCount(1);
    }
}
