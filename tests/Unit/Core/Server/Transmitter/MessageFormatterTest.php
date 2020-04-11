<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageFormatter;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;

class MessageFormatterTest extends TestCase
{
    public function testExceptionCouldNotEncodeJson()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not encode JSON');

        $writer = new MessageFormatter();
        $message = new ResponseMessage(1, [
            'hello' => \fopen('php://stdin', 'r')
        ]);

        $writer->write($message);
    }

    public function testWrite()
    {
        $writer = new MessageFormatter();
        $message = new ResponseMessage(1, [
            'hello' => 'goodbye'
        ]);

        $result = $writer->write($message);
        $this->assertEquals(
            "Content-Length: 66\r\n\r\n" . '{"id":1,"result":{"hello":"goodbye"},"error":null,"jsonrpc":"2.0"}',
            $result
        );
    }

    public function testWriteErrorReponse()
    {
        $writer = new MessageFormatter();
        $message = new ResponseMessage(1, [
            'hello' => 'goodbye'
        ], new ResponseError(ErrorCodes::InternalError, 'Sorry'));

        $result = $writer->write($message);
        $this->assertEquals(
            "Content-Length: 107\r\n\r\n" . '{"id":1,"result":{"hello":"goodbye"},"error":{"code":-32603,"message":"Sorry","data":null},"jsonrpc":"2.0"}',
            $result
        );
    }
}
