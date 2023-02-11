<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;

final class LspMessageSerializer implements MessageSerializer
{
    public function serialize(Message $message): string
    {
        $data = $this->normalize($message);
        if (!is_array($data)) {
            throw new RuntimeException('Expected an array');
        }
        if ($message instanceof ResponseMessage) {
            $data = $this->ensureOnlyResultOrErrorSet($data);
        }
        $decoded = json_encode($data);

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
     */
    public function normalize($message): mixed
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

    private function ensureOnlyResultOrErrorSet(array $data): array
    {
        if (array_key_exists('error', $data) && array_key_exists('result', $data)) {
            unset($data['result']);
            return $data;
        }

        if (!array_key_exists('error', $data) && !array_key_exists('result', $data)) {
            $data['result'] = null;
        }

        return $data;
    }
}
