<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Socket\ResourceSocket;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;

class TestClient
{
    /**
     * @var ResourceSocket
     */
    private $socket;

    public function __construct(ResourceSocket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @retrun Request[]
     */
    public function send(string $request): array
    {
        $this->socket->write($request);


        $responses = \Amp\Promise\wait(\Amp\call(function () {
            $reader = new LspMessageReader($this->socket);
            $responses = [];
            while (null !== $response = yield $reader->wait()) {
                $responses[] = $response;
            }

            return $responses;
        }));

        return $responses;
    }
}
