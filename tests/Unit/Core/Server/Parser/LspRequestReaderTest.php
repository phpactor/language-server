<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Parser;

use Amp\ByteStream\InMemoryStream;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Server\Parser\LspRequestReader;
use Phpactor\LanguageServer\Core\Rpc\Request;

class LspRequestReaderTest extends TestCase
{
    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    protected function setUp(): void
    {
    }

    public function testYieldsRequest()
    {
        $stream = new InMemoryStream(
            <<<EOT
Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",
   "id": 1,
   "method": "test",
   "params": {}
}
EOT
        );

        $reader = new LspRequestReader($stream);
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(Request::class, $result);
    }

    public function testReadsMultipleRequests()
    {
        $stream = new InMemoryStream(
            <<<EOT
Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",
   "id": 1,
   "method": "test",
   "params": {}
}Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",
   "id": 1,
   "method": "tset",
   "params": {}
}
EOT
        );
        $reader = new LspRequestReader($stream);
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(Request::class, $result, 'first');
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(Request::class, $result, 'second');
    }
}
