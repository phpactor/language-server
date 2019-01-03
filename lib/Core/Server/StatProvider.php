<?php

namespace Phpactor\LanguageServer\Core\Server;

interface StatProvider
{
    public function stats(): ServerStats;
}
