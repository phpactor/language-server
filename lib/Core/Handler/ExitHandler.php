<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Phpactor\LanguageServer\Core\Dispatcher\Handler;

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
    }
}
