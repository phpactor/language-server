<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use RuntimeException;

class MessageSerializer
{
    public function serialize(Message $message): string
    {
        $decoded = json_encode($this->normalize($message));

        if (false === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not encode JSON: "%s"',
                \json_last_error_msg()
            ));
        }

        return $decoded;
    }

    /**
     * Normalize a message before being serialized by recursively applying array_filter
     * and removing null values
     *
     * @param mixed $message
     *
     * @return mixed
     */
    private function normalize($message)
    {
        if (is_object($message)) {
            $message = (array) $message;
        }

        if (!is_array($message)) {
            return $message;
        }

        return array_filter(array_map([$this, 'normalize'], $message), function ($value) {
            return $value !== null;
        });
    }
}
