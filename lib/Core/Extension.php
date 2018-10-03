<?php

namespace Phpactor\LanguageServer\Core;

use LanguageServerProtocol\ServerCapabilities;

interface Extension
{
    public function handlers(): Handlers;

    public function configureCapabilities(ServerCapabilities $capabilities): void;
}
