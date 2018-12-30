<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;

class ResourceStreamProvider implements StreamProvider
{
    /**
     * @var ResourceDuplexStream
     */
    private $duplexStream;

    public function __construct(ResourceDuplexStream $duplexStream)
    {
        $this->duplexStream = $duplexStream;
    }

    public function provide(): Promise
    {
        return new Success($this->duplexStream);
    }
}
