<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Generator;

interface Handler
{
    public function methods(): array;
}
