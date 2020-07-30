<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Phpactor\LanguageServerProtocol\ServerCapabilities;

interface CanRegisterCapabilities
{
    public function registerCapabiltiies(ServerCapabilities $capabilities): void;
}
