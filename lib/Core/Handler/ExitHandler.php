<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;

class ExitHandler implements Handler
{
    public function methods(): array
    {
        return [
            'exit' => 'exit',
        ];
    }

    public function exit(): void
    {
        throw new ShutdownServer('Exit method invoked by client');
    }
}
