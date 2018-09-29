<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Handler;

class ExitServer implements Handler
{
    public function name(): string
    {
        return 'exit';
    }

    public function __invoke()
    {
        throw new ResetConnection('exit method invoked');
    }
}
