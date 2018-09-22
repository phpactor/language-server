<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\ChunkReader\BufferReader;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use RuntimeException;

class ServerTest extends TestCase
{
    private $dispatcher;

    /**
     * @var TestLogger
     */
    private $logger;

    /**
     * @var BufferReader
     */
    private $reader;

    /**
     * @var Server
     */
    private $server;

    public function setUp()
    {
        $this->dispatcher = $this->prophesize(Dispatcher::class);
        $this->logger = new TestLogger();
        $this->reader = new BufferReader();
        $this->server = new Server($this->logger, $this->dispatcher->reveal(), $this->reader);
    }

    public function testThrowsExceptionIfNoContentLengthProvided()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No valid Content-Length header provided in raw headers: ""');

        $payload = <<<EOT
 \r\n
 {
    "jsonrpc": "2.0",
    "id": 1,
    "method": "test",
    "params": {}
 }
EOT;
        $this->reader->write($payload);
        $this->server->start();
    }

    public function testStart()
    {
        $payload = <<<EOT
 Content-Length: 1234\r\n
 Content-Type: foo\r\n
 \r\n
 {
    "jsonrpc": "2.0",
    "id": 1,
    "method": "test",
    "params": {}
 }
EOT;
        $response = new ResponseMessage(2, new \stdClass());
        $this->dispatcher->dispatch(new RequestMessage(1, 'test', []))->willReturn($response);
        $this->reader->write($payload);
        $this->server->start();
        $response = $this->reader->read(10000);
        $this->assertEquals('{"id":2,"result":{},"responseError":null,"jsonRpc":"2.0"}', $response);
    }
}
