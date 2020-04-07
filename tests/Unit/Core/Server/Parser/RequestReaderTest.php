<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Parser;

use Amp\ByteStream\InMemoryStream;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;
use Phpactor\LanguageServer\Core\Rpc\Request;

class RequestReaderTest extends TestCase
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
        $stream = new InMemoryStream(<<<EOT
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

        $reader = new RequestReader($stream);
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(Request::class, $result);
    }

    public function testCallsbackWhenDataIsComplete()
    {
        $payload = <<<EOT
Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",

EOT
        ;
        $result = null;
        $parser = new RequestReader(function (Request $request) use (&$result) {
            $result = $request;
        });

        $parser->feed($payload);

        $payload = <<<'EOT'
   "id": 1,
   "method": "test",
   "params": {}
}
EOT;
        $parser->feed($payload);

        $this->assertInstanceOf(Request::class, $result);
    }

    public function testCallsBackMultipleTimes()
    {
        $results = [];
        $parser = new RequestReader(function (Request $request) use (&$results) {
            $results[] = $request;
        });

        $payload = <<<EOT
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
EOT;

        $parser->feed($payload);

        $this->assertCount(2, $results);
    }
}
