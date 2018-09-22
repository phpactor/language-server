<?php

namespace Phpactor\LanguageServer\Core;

use LanguageServerProtocol\ServerCapabilities;

interface LanguageServer
{
    public function capabilities(): ServerCapabilities;
}
