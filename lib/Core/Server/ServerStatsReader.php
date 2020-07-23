<?php

namespace Phpactor\LanguageServer\Core\Server;

use DateInterval;

interface ServerStatsReader
{
    public function uptime(): DateInterval;

    public function connectionCount(): int;

    public function requestCount(): int;
}
