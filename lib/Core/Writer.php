<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Transport\Request;

interface Writer
{
    public function writeResponse(IO $io, $response): void;
}
