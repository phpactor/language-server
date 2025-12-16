<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

final class LspMessageFormatter implements MessageFormatter
{
    public function __construct(private MessageSerializer $serializer = new LspMessageSerializer())
    {
    }

    public function format(Message $message): string
    {
        $body = $this->serializer->serialize($message);

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
}
