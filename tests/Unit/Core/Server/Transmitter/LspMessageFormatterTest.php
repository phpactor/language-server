<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageFormatter;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageSerializer;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class LspMessageFormatterTest extends TestCase
{
    /**
     * @dataProvider provideFormat
     */
    public function testFormat(string $serialized, int $expectedContentLength): void
    {
        $serializer = $this->prophesize(MessageSerializer::class);
        $formatter = new LspMessageFormatter($serializer->reveal());
        $message = new ResponseMessage(1, [
            'hello' => 'goodbye'
        ]);
        $serializer->serialize($message)->willReturn($serialized);

        $result = $formatter->format($message);

        $this->assertEquals(implode([
            'Content-Type: application/vscode-jsonrpc; charset=utf8',
            "\r\n",
            'Content-Length: ' . $expectedContentLength,
            "\r\n\r\n",
            $serialized
        ]), $result);
    }

    public function provideFormat()
    {
        yield [
            '',
            0
        ];
        yield [
            '0123456789',
            10,
        ];
    }
}
