<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Reader;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Exception\RequestError;
use Phpactor\LanguageServer\Core\IO\BufferIO;
use Phpactor\LanguageServer\Core\Reader\LanguageServerProtocolReader;
use Phpactor\LanguageServer\Tests\Unit\Core\TestLogger;

class LanguageServerReaderTest extends TestCase
{
    /**
     * @var BufferIO
     */
    private $io;

    /**
     * @var LanguageServerReader
     */
    private $reader;

    public function setUp()
    {
        $this->io = new BufferIO();
        $this->logger = new TestLogger();
        $this->reader = new LanguageServerProtocolReader(
            $this->logger
        );
    }

    public function testExceptionIfNoContentLengthProvided()
    {
        $this->expectException(RequestError::class);
        $payload = <<<EOT
 \r\n
 {
    "jsonrpc": "2.0",
    "id": 1,
    "method": "test",
    "params": {}
 }
EOT;
        $this->io->add($payload);
        $this->reader->readRequest($this->io);
    }

    public function testRead()
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
        $this->io->add($payload);
        $request = $this->reader->readRequest($this->io);
        $this->assertEquals(json_decode('{"jsonrpc":"2.0","method":"test","params":{},"id":1}'), json_decode($request->body()));
    }

    private function assertLogMessage(string $string)
    {
        $messages = implode(PHP_EOL, $this->logger->messages());
        $this->assertContains($string, $messages);
    }
}
