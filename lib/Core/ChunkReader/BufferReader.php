<?php

namespace Phpactor\LanguageServer\Core\ChunkReader;

use Phpactor\LanguageServer\Core\ChunkReader;

class BufferReader implements ChunkReader
{
    private $buffer = [];
    private $index = 0;

    public function write(string $text)
    {
        $this->buffer += str_split($text);
    }

    public function read(int $size): string
    {
        $buffer = [];
        for ($i = 0; $i < $size; $i++) {
            $buffer[] = array_shift($this->buffer);
        }

        return implode('', $buffer);
    }
}
