<?php

namespace Phpactor\LanguageServer\Core\IO;

use Phpactor\LanguageServer\Core\Chunk;
use Phpactor\LanguageServer\Core\IO;
use RuntimeException;

class StreamIO implements IO
{
    private $inStream;
    private $outStream;

    public function __construct($inStream, $outStream)
    {
        $this->validateStream($inStream);
        $this->validateStream($outStream);
        $this->inStream = $inStream;
        $this->outStream = $outStream;
    }

    public function read(int $size): Chunk
    {
        $contents = fread($this->inStream, $size);

        return new Chunk($contents === false || '' === $contents ? null : $contents);
    }

    public function write(string $string)
    {
        fwrite($this->outStream, $string);
    }

    private function validateStream($stream)
    {
        stream_set_blocking($stream, true);
        if (!is_resource($stream)) {
            throw new RuntimeException(sprintf(
                'Given stream is not a resource, is a "%s"',
                gettype($stream)
            ));
        }
    }
}
