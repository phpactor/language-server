<?php

namespace Phpactor\LanguageServer\Core;

interface Handler
{
    public function name(): string;

    public function __invoke(...$parameters);
}
