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

    public function testThrowsExitSessionException()
    {
        $this->expectException(ExitSession::class);
        $this->dispatch('exit', []);
    }
}
