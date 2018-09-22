<?php

namespace Phpactor\LanguageServer\Core\ChunkIO;

use Phpactor\LanguageServer\Core\Chunk;
use Phpactor\LanguageServer\Core\ChunkIO;
use RuntimeException;

class StreamIO implements ChunkIO
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

        return new Chunk($contents === false ? null : $contents);
    }

    public function write(string $string)
    {
        fwrite($this->outStream, $string);
    }

    private function validateStream($inStream)
    {
        if (!is_resource($inStream)) {
            throw new RuntimeException(sprintf(
                'Given stream is not a resource, is a "%s"',
                gettype($stream)
            ));
        }
    }
}
