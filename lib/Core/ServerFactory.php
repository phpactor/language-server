<?php

namespace Phpactor\LanguageServer\Core;

interface ServerFactory
{
    public function server(): Server;
}
