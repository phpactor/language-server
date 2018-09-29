<?php

namespace Phpactor\LanguageServer\Core;

interface IO
{
    public function read(int $size): string;

    public function write(string $string);
}
