<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServer\Core\Workspace\Workspace;

class LanguageServerTesterServiceProvider
{
    public function __construct()
    {
        $this->workspace = new Workspace();
    }
}
