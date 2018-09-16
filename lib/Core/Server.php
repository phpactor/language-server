<?php

namespace Phpactor\LanguageServer\Core;

use LanguageServerProtocol\ServerCapabilities;

interface Server
{
    public function capabilities(): ServerCapabilities;
}
