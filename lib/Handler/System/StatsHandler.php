<?php

namespace Phpactor\LanguageServer\Handler\System;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ServerStatsReader;

class StatsHandler implements Handler
{
    const METHOD_STATUS = 'phpactor/stats';

    /**
     * @var ServerStatsReader
     */
    private $statProvider;

    /**
     * @var ClientApi
     */
    private $client;

    public function __construct(ClientApi $client, ServerStatsReader $statProvider)
    {
        $this->statProvider = $statProvider;
        $this->client = $client;
    }

    public function methods(): array
    {
        return [
            self::METHOD_STATUS => 'status',
        ];
    }

    /**
     * @return Promise<null>
     */
    public function status(): Promise
    {
        $this->client->window()->showMessage()->info(
            implode(', ', [
                'pid: ' . getmypid(),
                'up: ' . $this->statProvider->uptime()->format('%ad %hh %im %ss'),
                'connections: ' . $this->statProvider->connectionCount(),
                'requests: ' . $this->statProvider->requestCount(),
                'mem: ' . number_format(memory_get_peak_usage()) . 'b',
            ])
        );

        return new Success(null);
    }
}
