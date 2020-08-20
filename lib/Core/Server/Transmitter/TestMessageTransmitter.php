<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Countable;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use RuntimeException;

final class TestMessageTransmitter implements MessageTransmitter, TestMessageTransmitterStack, Countable
{
    /**
     * @var Message[]
     */
    private $buffer = [];

    public function __construct(Message ...$buffer)
    {
        $this->buffer = $buffer;
    }

    public function transmit(Message $response): void
    {
        $this->buffer[] = $response;
    }

    public function filterByMethod(string $method): self
    {
        return new self(...array_filter($this->buffer, function (Message $message) use ($method) {
            if (
                !$message instanceof RequestMessage &&
                !$message instanceof NotificationMessage
            ) {
                return false;
            }

            return $message->method === $method;
        }));
    }

    public function shift(): ?Message
    {
        return array_shift($this->buffer);
    }

    public function shiftNotification(): ?NotificationMessage
    {
        $message = array_shift($this->buffer);

        if (null === $message) {
            return null;
        }

        if (!$message instanceof NotificationMessage) {
            throw new RuntimeException(sprintf(
                'Expected NotificationMessage, got "%s"',
                get_class($message)
            ));
        }

        return $message;
    }

    public function shiftRequest(): ?RequestMessage
    {
        $message = array_shift($this->buffer);

        if (null === $message) {
            return null;
        }

        if (!$message instanceof RequestMessage) {
            throw new RuntimeException(sprintf(
                'Expected RequestMessage, got "%s"',
                get_class($message)
            ));
        }

        return $message;
    }

    public function clear(): void
    {
        $this->buffer = [];
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->buffer);
    }
}
