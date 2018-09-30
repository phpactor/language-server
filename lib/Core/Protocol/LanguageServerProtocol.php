<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Protocol;
use Phpactor\LanguageServer\Core\Reader;
use Phpactor\LanguageServer\Core\Transport\Request;
use Phpactor\LanguageServer\Core\Writer;
use Psr\Log\LoggerInterface;

class LanguageServerProtocol implements Protocol
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    public function __construct(Reader $reader, Writer $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public function readRequest(IO $io): Request
    {
        return $this->reader->readRequest($io);
    }

    public function writeResponse(IO $io, $response): void
    {
        $this->writer->writeResponse($io, $response);
    }

    public static function create(LoggerInterface $logger)
    {
        return new self(
            new LanguageServerProtocolReader($logger),
            new LanguageServerProtocolWriter($logger)
        );
    }
}
