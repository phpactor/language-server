<?php

namespace Phpactor\LanguageServer\Core;

interface IO
{
    public function read(int $size): Chunk;

    public function write(string $string);
}
