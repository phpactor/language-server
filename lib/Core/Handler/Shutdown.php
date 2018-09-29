<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Handler;

class Shutdown implements Handler
{
    public function name(): string
    {
        return 'shutdown';
    }

    public function __invoke()
    {
        throw new ShutdownServer('shutdown method invoked');
    }
}
