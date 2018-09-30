<?php

namespace Phpactor\LanguageServer\Core\IO;

use Phpactor\LanguageServer\Core\IO;

class DummyIO implements IO
{
    public function read(int $size): string
    {
    }

    public function write(string $string)
    {
    }
}
