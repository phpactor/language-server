<?php

namespace Phpactor\LanguageServer\Core\IO;

use Phpactor\LanguageServer\Core\Exception\RequestError;
use Phpactor\LanguageServer\Core\IO;
use RuntimeException;

class StreamIO implements IO
{
    const SLEEP_TIME = 100000;
    const CHUNK_SIZE = 100;

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

        if (false === $contents) {
            throw new RequestError(
                'Could not read from stream'
            );
        }

        $contents = $this->readAnyRemainingBytes($contents, $size);

        return $contents;
    }

    public function write(string $string)
    {
        $written = 0;

        // write to the stream in chunks, as for some reason writing to STDOUT
        // truncates at around 36,840 bytes and disabling stream buffering and
        // flushing the stream seems to have no effect.
        while ($written < strlen($string)) {
            $written += fwrite($this->outStream, substr($string, $written, self::CHUNK_SIZE));
        }
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

    private function readAnyRemainingBytes(string $contents, int $size)
    {
        $remaining = $size - strlen($contents);
        
        while ($remaining) {
            $contents .= fread($this->inStream, $remaining);
            $remaining = $size - strlen($contents);
        }

        return $contents;
    }
}
