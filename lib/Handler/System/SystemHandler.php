<?php

namespace Phpactor\LanguageServer\Handler\System;

use LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\StatProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;

class SystemHandler implements Handler
{
    const METHOD_STATUS = 'system/status';

    /**
     * @var StatProvider
     */
    private $statProvider;

    public function __construct(StatProvider $statProvider)
    {
        $this->statProvider = $statProvider;
    }

    public function methods(): array
    {
        return [
            self::METHOD_STATUS => 'status',
        ];
    }

    public function status()
    {
        yield null;
        yield new NotificationMessage('window/showMessage', [
            'type' => MessageType::INFO,
            'message' => implode(', ', [
                'up: ' . $this->statProvider->stats()->uptime->format('%ad %hh %im %ss'),
                'connections: ' . $this->statProvider->stats()->connectionCount,
                'requests: ' . $this->statProvider->stats()->requestCount,
                'mem: ' . number_format(memory_get_peak_usage()) . 'b',
            ]),
        ]);
    }
}
