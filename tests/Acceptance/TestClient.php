<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Socket\ClientSocket;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;

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

    public function send(string $request)
    {
        $parser = new LanguageServerProtocolParser();
        $this->socket->write($request);
        $rawResponse = \Amp\Promise\Wait($this->socket->read());

        return array_filter(iterator_to_array($parser->feed($rawResponse)));
    }
}
