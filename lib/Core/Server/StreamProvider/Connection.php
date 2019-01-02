<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;

class Connection
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DuplexStream
     */
    private $stream;

    public function __construct(string $id, DuplexStream $stream)
    {
        $this->id = $id;
        $this->stream = $stream;
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
