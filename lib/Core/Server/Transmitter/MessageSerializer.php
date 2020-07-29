<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

interface MessageSerializer
{
    public function serialize(Message $message): string;

    /**
     * Normalize a message before being serialized by recursively applying array_filter
     * and removing null values
     *
     * @param mixed $message
     *
     * @return mixed
     */
    public function normalize($message);
}
