<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Parser;

use Amp\ByteStream\InMemoryStream;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;

class LspMessageReaderTest extends TestCase
{
    /**
     * @var LanguageServerProtocolParser
     */
    private $parser;

    protected function setUp(): void
    {
    }

    public function testYieldsRequest(): void
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

        $reader = new LspMessageReader($stream);
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(RawMessage::class, $result);
    }

    public function testReadsMultipleRequests(): void
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
        $reader = new LspMessageReader($stream);
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(RawMessage::class, $result, 'first');
        $result = \Amp\Promise\wait($reader->wait());
        $this->assertInstanceOf(RawMessage::class, $result, 'second');
    }
}
