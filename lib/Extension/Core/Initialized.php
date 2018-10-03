<?php

namespace Phpactor\LanguageServer\Extension\Core;

use Phpactor\LanguageServer\Core\Handler;

class Initialized implements Handler
{
    public function name(): string
    {
        return 'initialized';
    }

    public function __invoke()
    {
    }
}
