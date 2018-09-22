<?php

namespace Phpactor\LanguageServer\Core;

interface LanguageServerFactory
{
    public function server(): LanguageServer;
}
