<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Transport\Request;

interface Reader
{
    public function readRequest(IO $io): Request;
}
