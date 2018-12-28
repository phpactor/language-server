<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Transport\Request;

class LanguageServerProtocolParserTest extends TestCase
{
    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    public function setUp()
    {
    }

    public function testYieldsRequestOnCompleteData()
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
        $parser = (new LanguageServerProtocolParser())->__invoke();
        $request = $parser->send($payload);
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testYieldsRequestWhenDataIsComplete()
    {
        $parser = (new LanguageServerProtocolParser())->__invoke();

        $payload = <<<EOT
Content-Length: 74\r\n
Content-Type: foo\r\n\r\n
{
   "jsonrpc": "2.0",

EOT
        ;

        $request = $parser->send($payload);
        $this->assertNull($request);

        $payload = <<<'EOT'
   "id": 1,
   "method": "test",
   "params": {}
}
EOT;
        $request = $parser->send($payload);
        $this->assertInstanceOf(Request::class, $request);
    }
}
