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
     * @var string
     */
    private $inStreamName;

    /**
     * @var string
     */
    private $outStreamName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var resource
     */
    private $inStream;

    /**
     * @var resource
     */
    private $outStream;

    public function __construct(LoggerInterface $logger, string $inStream = 'php://stdin', string $outStream = 'php://stdout')
    {
        $this->logger = $logger;

        $this->inStreamName = $inStream;
        $this->outStreamName = $outStream;
    }

    public function accept(): IO
    {
        $this->logger->info('opening streams', [
            'in' => $this->inStreamName,
            'out' => $this->outStreamName
        ]);


        $inStream = fopen($this->inStreamName, 'r');
        $outStream = fopen($this->outStreamName, 'w');

        if (false === $inStream) {
            throw new RuntimeException(sprintf(
                'Could not open stream: ' . $this->inStreamName
            ));
        }

        if (false === $outStream) {
            throw new RuntimeException(sprintf(
                'Could not open stream: ' . $this->outStreamName
            ));
        }

        $this->inStream = $inStream;
        $this->outStream = $outStream;

        return new StreamIO($this->inStream, $this->outStream);
    }

    public function shutdown()
    {
        $this->logger->info('closing streams', [
            'in' => $this->inStreamName,
            'out' => $this->outStreamName
        ]);
        fclose($this->inStream);
        fclose($this->outStream);
    }

    public function reset(): void
    {
        $this->shutdown();
    }

    private function validateStream($stream): void
    {
    }
}
