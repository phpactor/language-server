<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Transmitter;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageSerializer;
use RuntimeException;

class LspMessageSerializerTest extends TestCase
{
    public function testExceptionCouldNotEncodeJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not encode JSON');

        $message = new ResponseMessage(1, [
            'hello' => \fopen('php://stdin', 'r')
        ]);

        $this->serialize($message);
    }

    /**
     * @dataProvider provideSerializes
     */
    public function testSerializes(Message $message, string $expected): void
    {
        self::assertJsonStringEqualsJsonString($expected, $this->serialize($message));
    }

    public function provideSerializes()
    {
        yield 'response message' => [
            new ResponseMessage(1, [], new ResponseError(1, 'foobar', [])),
            '{"id":1,"result":[],"error":{"code":1,"message":"foobar","data":[]},"jsonrpc":"2.0"}',
        ];

        yield 'response message with null result' => [
            new ResponseMessage(1, null),
            '{"id":1,"jsonrpc":"2.0","result":null}',
        ];

        yield 'preserves falsey values' => [
            new ResponseMessage(0, ''),
            '{"id":0,"result":"","jsonrpc":"2.0"}',
        ];

        yield 'removes nested null' => [
            new NotificationMessage('showMessage', [
                'foobar' => [
                    'notnull' => 'notnull',
                    'null' => null,
                ],
            ]),
            '{"method":"showMessage","params":{"foobar":{"notnull":"notnull"}},"jsonrpc":"2.0"}',
        ];
    }

    private function serialize(Message $message): string
    {
        return (new LspMessageSerializer())->serialize($message);
    }
}
