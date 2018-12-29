<?php

namespace Phpactor\LanguageServer\Extension\Core;

use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;

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
