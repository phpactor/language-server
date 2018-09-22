<?php

namespace Phpactor\LanguageServer\Core\ChunkReader;

use InvalidArgumentException;
use Phpactor\LanguageServer\Core\ChunkReader;

class StreamReader implements ChunkReader
{
    private $resource;

    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a valid resource, got "%s"', gettype($resource)
            ));
        }
        $this->resource = $resource;
    }

    public function read(int $size): string
    {
        yield fread($this->resource, $size);
    }
}
