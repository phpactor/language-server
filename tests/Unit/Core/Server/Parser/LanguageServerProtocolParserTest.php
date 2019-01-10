<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Rpc\Request;

class LanguageServerProtocolParserTest extends TestCase
{
    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    public function setUp()
    {
    }

    public function testCallsbackOnCompletedData()
    {
        $payload = <<<EOT
Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",
   "id": 1,
   "method": "test",
   "params": {}
}
EOT;
        $request = null;
        $parser = new LanguageServerProtocolParser(function (Request $request) use (&$result) {
            $result = $request;
        });
        $parser->feed($payload);
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
        $parser = new LanguageServerProtocolParser(function (Request $request) use (&$result) {
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
        $parser = new LanguageServerProtocolParser(function (Request $request) use (&$results) {
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
