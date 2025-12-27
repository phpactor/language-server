<?php

namespace Phpactor\LanguageServer\Core\Server;

use DateInterval;
use DateTimeImmutable;

/**
 * Class which can be passed _to_ the server in order to collect statistics.
 */
final class ServerStats implements ServerStatsReader
{
    public function __construct(
        private DateTimeImmutable $created = new DateTimeImmutable(),
        private int $connectionCount = 0,
        private int $requestCount = 0,
    ) {
    }

    public function incConnectionCount(): void
    {
        $this->connectionCount++;
    }

    public function decConnectionCount(): void
    {
        $this->connectionCount--;
    }

    public function incRequestCount(): void
    {
        $this->requestCount++;
    }

    public function uptime(): DateInterval
    {
        return $this->created->diff(new DateTimeImmutable());
    }

    public function connectionCount(): int
    {
        return $this->connectionCount;
    }

    public function requestCount(): int
    {
        return $this->requestCount;
    }
}
