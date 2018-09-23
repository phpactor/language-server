<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\IO;

interface Reader
{
    public function readRequest(IO $io): string;
}
