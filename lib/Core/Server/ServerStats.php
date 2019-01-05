<?php

namespace Phpactor\LanguageServer\Core\Server;

use DateInterval;

class ServerStats
{
    /**
     * @var DateInterval
     */
    public $uptime;

    /**
     * @var int
     */
    public $connectionCount;

    /**
     * @var int
     */
    public $requestCount;

    public function __construct(DateInterval $uptime, int $connectionCount, int $requestCount)
    {
        $this->uptime = $uptime;
        $this->connectionCount = $connectionCount;
        $this->requestCount = $requestCount;
    }
}
