<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Generator;
use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;

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
