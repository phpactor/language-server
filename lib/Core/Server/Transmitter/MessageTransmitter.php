<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

interface MessageTransmitter
{
    public function transmit(Message $response): void;
}
