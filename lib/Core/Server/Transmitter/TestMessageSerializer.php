<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

class TestMessageSerializer implements MessageSerializer
{
    public function __construct(private string $serialized)
    {
    }

    public function serialize(Message $message): string
    {
        return $this->serialized;
    }

    public function normalize($message)
    {
        return $message;
    }
}
