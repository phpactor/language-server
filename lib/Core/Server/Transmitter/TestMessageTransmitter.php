<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use RuntimeException;

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

    public function shiftNotification(): NotificationMessage
    {
        $message = array_shift($this->buffer);
        if (!$message instanceof NotificationMessage) {
            throw new RuntimeException(sprintf(
                'Expected NotificationMessage, got "%s"',
                get_class($message)
            ));
        }

        return $message;
    }

    public function shiftRequest(): RequestMessage
    {
        $message = array_shift($this->buffer);
        if (!$message instanceof RequestMessage) {
            throw new RuntimeException(sprintf(
                'Expected RequestMessage, got "%s"',
                is_object($message) ? get_class($message) : gettype($message)
            ));
        }

        return $message;
    }

    public function clear(): void
    {
        $this->buffer = [];
    }
}
