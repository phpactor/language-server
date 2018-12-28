<?php

namespace Phpactor\LanguageServer\Core;

use Generator;

/**
 * @method Generator|null __invoke()
 */
interface Handler
{
    public function name(): string;
}
