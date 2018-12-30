<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

interface Handler
{
    public function methods(): array;
}
