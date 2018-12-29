<?php

namespace Phpactor\LanguageServer\Extension\Core;

use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;

class ExitServer implements Handler
{
    public function name(): string
    {
        return 'exit';
    }

    public function __invoke(): Generator
    {
        yield new NotificationMessage(
            'window/showMessage',
            [
                'type' => 4,
                'message' => 'Phpactor is shutting down'
            ]
        );
        throw new ShutdownServer('exit method invoked');
    }
}
