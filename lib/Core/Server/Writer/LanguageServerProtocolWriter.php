<?php

namespace Phpactor\LanguageServer\Core\Server\Writer;

use Phpactor\LanguageServer\Core\Rpc\Message;
use RuntimeException;

final class LanguageServerProtocolWriter
{
    public function write(Message $message): string
    {
        $body = json_encode($message);

        if (false === $body) {
            throw new RuntimeException(sprintf(
                'Could not encode JSON: "%s"',
                \json_last_error_msg()
            ));
        }

        $headers = [
            'Content-Length: ' . strlen($body),
        ];

        return implode('', [
            implode("\r\n", $headers),
            "\r\n\r\n",
            $body
        ]);
    }
}
