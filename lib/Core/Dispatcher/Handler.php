<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Generator;

/**
 * @method Generator|null __invoke()
 */
interface Handler
{
    public function name(): string;
}
