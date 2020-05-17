<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\RpcClient;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;

class JsonRpcClientTest extends TestCase
{
    public function testRequestIsSerializable(): void
    {
        $watcher = new TestResponseWatcher();
        $transmitter = new TestMessageTransmitter();
        $client = new JsonRpcClient($transmitter, $watcher);

        $client->request('foobar', []);

        // request is serializable
        $request = $transmitter->shiftRequest();

        $encoded = json_encode($request);
        self::assertIsString($encoded);
    }
}
