<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server\Writer;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class LanguageServerProtocolWriterTest extends TestCase
{
    public function testWrite()
    {
        $writer = new LanguageServerProtocolWriter();
        $message = new ResponseMessage(1, [
            'hello' => 'goodbye'
        ]);

        $result = $writer->write($message);
        $this->assertEquals(
            "Content-Length: 74\r\n\r\n" . '{"id":1,"result":{"hello":"goodbye"},"responseError":null,"jsonrpc":"2.0"}',
            $result
        );
    }
}
