<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Socket\ClientSocket;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Transport\Request;

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

    public function send(string $request): Request
    {
        $parser = (new LanguageServerProtocolParser())->__invoke();
        $this->socket->write($request);
        $rawResponse = \Amp\Promise\Wait($this->socket->read());

        return $parser->send($rawResponse);
    }
}
