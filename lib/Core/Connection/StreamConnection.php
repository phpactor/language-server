<?php

namespace Phpactor\LanguageServer\Core\Connection;

use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
        $inStream = fopen($inStream, 'r');
        $outStream = fopen($outStream, 'w');

        $this->validateStream($inStream);
        $this->validateStream($outStream);

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

    private function validateStream($stream): void
    {
        if (false === $stream) {
            throw new RuntimeException(sprintf(
                'Could not open stream'
            ));
        }
    }
}
