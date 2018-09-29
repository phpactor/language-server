<?php

namespace Phpactor\LanguageServer\Core\IO;

use Phpactor\LanguageServer\Core\Chunk;
use Phpactor\LanguageServer\Core\IO;
use RuntimeException;

class StreamIO implements IO
{
    const SLEEP_TIME = 100000;

    private $inStream;
    private $outStream;

    public function __construct($inStream, $outStream)
    {
        $this->validateStream($inStream);
        $this->validateStream($outStream);
        $this->inStream = $inStream;
        $this->outStream = $outStream;
    }

    public function read(int $size): string
    {
        while ('' === $contents = fread($this->inStream, $size)) {
            usleep(self::SLEEP_TIME);
        }

        return $contents;
    }

    public function write(string $string)
    {
        fwrite($this->outStream, $string);
    }

    private function validateStream($stream)
    {
        if (!is_resource($stream)) {
            throw new RuntimeException(sprintf(
                'Given stream is not a resource, is a "%s"',
                gettype($stream)
            ));
        }
    }
}
