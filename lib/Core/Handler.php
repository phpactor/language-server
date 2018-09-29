<?php

namespace Phpactor\LanguageServer\Core;

use Generator;

/**
 * @method ?Generator __invoke()
 */
interface Handler
{
    public function name(): string;
}
