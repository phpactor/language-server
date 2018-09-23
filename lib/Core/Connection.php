<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\IO;

interface Connection
{
    public function io(): IO;

    public function shutdown();
}
