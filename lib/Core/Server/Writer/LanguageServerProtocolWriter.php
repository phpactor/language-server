<?php

namespace Phpactor\LanguageServer\Core\Server\Writer;

use Phpactor\LanguageServer\Core\Rpc\Message;

final class LanguageServerProtocolWriter
{
    public function write(Message $message): string
    {
        $body = json_encode($message);

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
