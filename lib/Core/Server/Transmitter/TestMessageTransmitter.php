<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

class TestMessageTransmitter implements MessageTransmitter, TestMessageTransmitterStack
{
    /**
     * @var Message[]
     */
    private $buffer = [];

    public function transmit(Message $response): void
    {
        $this->buffer[] = $response;
    }

    public function shift(): ?Message
    {
        return array_shift($this->buffer);
    }
}
