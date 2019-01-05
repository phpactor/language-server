<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\ServerCapabilities;

interface CanRegisterCapabilities
{
    public function registerCapabiltiies(ServerCapabilities $capabilities);
}
