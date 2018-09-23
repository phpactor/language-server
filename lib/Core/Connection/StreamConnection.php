<?php

namespace Phpactor\LanguageServer\Core\Connection;

use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Psr\Log\LoggerInterface;

class StreamConnection implements Connection
{
    /**
     * @var resource
     */
    private $inStream;

    /**
     * @var resource
     */
    private $outStream;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, string $inStream = 'php://stdin', string $outStream = 'php://stdout')
    {
        $this->inStream = fopen($inStream, 'r');
        $this->outStream = fopen($outStream, 'w');
        $this->logger = $logger;

        $this->logger->info('listening on stdio', [
            'in' => $inStream,
            'out' => $outStream
        ]);
    }

    public function io(): IO
    {
        return new StreamIO($this->inStream, $this->outStream);
    }

    public function shutdown()
    {
        $this->logger->info('shutting down streams', [
            'in' => $this->inStream,
            'out' => $this->outStream
        ]);
        fclose($this->inStream);
        fclose($this->outStream);
    }

    public function reset(): void
    {
    }
}
