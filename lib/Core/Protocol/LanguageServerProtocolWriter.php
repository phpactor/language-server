<?php

namespace Phpactor\LanguageServer\Core\Protocol;

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
        $length = strlen($response);
        $out = "Content-Length:{$length}\r\n\r\n{$response}";
        $this->logger->debug('OUT: ' . $out);
        $io->write($out);
    }
}
