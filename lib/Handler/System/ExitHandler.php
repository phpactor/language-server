<?php

namespace Phpactor\LanguageServer\Handler\System;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;

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
        throw new ExitSession('Exit method invoked by client');
    }
}
