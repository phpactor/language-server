<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Psr\Log\LoggerInterface;

class ResourceStreamProvider implements StreamProvider
{
    /**
     * @var ResourceDuplexStream
     */
    private $duplexStream;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ResourceDuplexStream $duplexStream, LoggerInterface $logger)
    {
        $this->duplexStream = $duplexStream;
        $this->logger = $logger;
    }

    public function provide(): Promise
    {
        $this->logger->info('Listening on STDIO');
        return new Success($this->duplexStream);
    }
}
