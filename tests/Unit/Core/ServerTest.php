<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\ChunkIO\BufferIO;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Exception\ServerError;
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
     * @var BufferIO
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
        $this->reader = new BufferIO();
        $this->server = new Server(
            $this->logger,
            $this->dispatcher->reveal(),
            $this->reader,
            1
        );
    }

    public function testLogsErrorIfNoContentLengthProvided()
    {
        $payload = <<<EOT
 \r\n
 {
    "jsonrpc": "2.0",
    "id": 1,
    "method": "test",
    "params": {}
 }
EOT;
        $this->reader->add($payload);
        $this->server->start();
        $this->assertLogMessage('[error] No valid Content-Length header provided in raw headers');
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
        $this->reader->add($payload);

        $this->server->start();
        $response = $this->reader->out();
        $this->assertEquals('{"id":2,"result":{},"responseError":null,"jsonRpc":"2.0"}', $response);
    }

    private function assertLogMessage(string $string)
    {
        $messages = implode(PHP_EOL, $this->logger->messages());
        $this->assertContains($string, $messages);
    }
}
