<?php

namespace Phpactor\LanguageServer\Core\ChunkIO;

use Phpactor\LanguageServer\Core\ChunkIO;

class BufferIO implements ChunkIO
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
