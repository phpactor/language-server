<?php

namespace Phpactor\LanguageServer\Core;

interface ChunkReader
{
    public function read(int $size): string;

    public function write(string $string);
}
