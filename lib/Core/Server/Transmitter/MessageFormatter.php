<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use RuntimeException;

final class MessageFormatter
{
    public function write(Message $message): string
    {
        $body = json_encode($this->normalize($message));

        if (false === $body) {
            throw new RuntimeException(sprintf(
                'Could not encode JSON: "%s"',
                \json_last_error_msg()
            ));
        }

        $headers = [
            'Content-Type: application/vscode-jsonrpc; charset=utf8',
            'Content-Length: ' . strlen($body),
        ];

        return implode('', [
            implode("\r\n", $headers),
            "\r\n\r\n",
            $body
        ]);
    }

    /**
     * Normalize a message before being serialized by recursively applying array_filter.
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

        return array_filter(array_map([$this, 'normalize'], $message));
    }
}
