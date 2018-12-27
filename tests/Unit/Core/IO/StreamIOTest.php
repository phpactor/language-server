<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\IO;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use RuntimeException;

class StreamIOTest extends TestCase
{
    const EXAMPLE_LONG_LENGTH = 8200;

    public function testReadStream()
    {
        $resource = fopen('php://temporary', 'rw');
        fwrite($resource, str_repeat('x', self::EXAMPLE_LONG_LENGTH));
        rewind($resource);

        $streamIo = new StreamIO($resource, $resource);
        $result = $streamIo->read(self::EXAMPLE_LONG_LENGTH);
        $this->assertEquals(self::EXAMPLE_LONG_LENGTH, strlen($result));
    }

    public function testThrowsExceptioninInvalidInStream()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given stream is not a resource, is a "string"');
        $resource = 'hello';
        $stream = fopen('php://temporary', 'rw');
        $streamIo = new StreamIO($resource, $stream);
    }

    public function testThrowsExceptioninInvalidOutStream()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given stream is not a resource, is a "string"');
        $string = 'hello';
        $stream = fopen('php://temporary', 'rw');
        $streamIo = new StreamIO($stream, $string);
    }
}
