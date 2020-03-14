<?php

namespace Phpactor\LanguageServer\Handler\Example;

use Amp\Deferred;
use Amp\Delayed;
use Amp\Success;
use Generator;
use LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

class PingHandler implements ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping' => 'ping'
        ];
    }

    public function ping(MessageTransmitter $transmitter): Generator
    {
        while (true) {
            yield new Delayed(1000);
            $transmitter->transmit(new NotificationMessage('window/logMessage', [
                'type' => MessageType::INFO,
                'message' => 'ping',
            ]));
        }
    }
}
