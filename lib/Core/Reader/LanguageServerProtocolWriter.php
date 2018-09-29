<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Writer;
use Psr\Log\LoggerInterface;

class LanguageServerProtocolWriter implements Writer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function writeResponse(IO $io, $response): void
    {
        $length = mb_strlen($response);
        $io->write("Content-Length:{$length}\r\n\r\n{$response}");
    }
}
