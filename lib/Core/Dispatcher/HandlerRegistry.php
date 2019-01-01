<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

interface HandlerRegistry
{
    public function get(string $name): Handler;
}
