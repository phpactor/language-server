<?php

namespace Phpactor\LanguageServer\Core;

interface ChunkIO
{
    public function read(int $size): string;

    public function write(string $string);
}
