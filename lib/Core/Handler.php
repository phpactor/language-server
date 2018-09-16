<?php

namespace Phpactor\LanguageServer\Core;

/**
 * @method ResponseMessage __invoke()
 */
interface Handler
{
    public function name(): string;
}
