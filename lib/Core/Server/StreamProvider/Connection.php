<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;

final class Connection
{
    public function __construct(private string $id, private DuplexStream $stream)
    {
    }

    public function stream(): DuplexStream
    {
        return $this->stream;
    }

    public function id(): string
    {
        return $this->id;
    }
}
