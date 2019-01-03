<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use IteratorAggregate;

interface HandlerCollection extends IteratorAggregate
{
    public function get(string $handler): Handler;
}
