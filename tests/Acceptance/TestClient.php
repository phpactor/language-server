<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Socket\ClientSocket;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Rpc\Request;

class TestClient
{
    /**
     * @var ClientSocket
     */
    private $socket;

    public function __construct(ClientSocket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @retrun Request[]
     */
    public function send(string $request): array
    {
        $responses = [];
        $this->socket->write($request);

        $parser = new LanguageServerProtocolParser(function (Request $request) use (&$responses) {
            $responses[] = $request;
        });

        $rawResponse = \Amp\Promise\Wait($this->socket->read());
        $parser->feed($rawResponse);

        return $responses;
    }
}
