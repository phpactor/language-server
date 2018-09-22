<?php

namespace Phpactor\LanguageServer\Core;

interface ChunkIO
{
    public function read(int $size): Chunk;

    public function write(string $string);
}
