<?php

namespace Phpactor\LanguageServer\Core\IO;

use Phpactor\LanguageServer\Core\IO;

class BufferIO implements IO
{
    private $buffer = [];
    private $index = 0;
    private $out = '';

    public function add(string $text)
    {
        $this->buffer = array_merge($this->buffer, str_split($text));
    }

    public function read(int $size): string
    {
        $buffer = [];
        for ($i = 0; $i < $size; $i++) {
            $buffer[] = array_shift($this->buffer);
        }

        return implode('', $buffer);
    }

    public function write(string $string)
    {
        $this->out .= $string;
    }

    public function out(): string
    {
        return $this->out;
    }
}
