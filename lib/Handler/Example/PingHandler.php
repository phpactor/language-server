<?php

namespace Phpactor\LanguageServer\Handler\Example;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\MessageType;
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
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping'
        ];
    }

    /**
     * @return Promise<null>
     */
    public function ping(MessageTransmitter $transmitter, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($transmitter, $cancel) {
            while (true) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    break;
                }
                yield new Delayed(1000);
                $transmitter->transmit(new NotificationMessage('window/logMessage', [
                    'type' => MessageType::INFO,
                    'message' => 'ping',
                ]));
            }
        });
    }
}
