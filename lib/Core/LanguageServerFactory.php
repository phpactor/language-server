<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\LanguageServer;

interface LanguageServerFactory
{
    public function server(): LanguageServer;
}
