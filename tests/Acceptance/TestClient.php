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
        $parser = (new LanguageServerProtocolParser())->__invoke();
        $this->socket->write($request);
        $rawResponse = \Amp\Promise\Wait($this->socket->read());

        $responses = [];
        while ($response = $parser->send($rawResponse)) {
            $responses[] = $response;
            $rawResponse = '';
        }

        return $responses;
    }
}
