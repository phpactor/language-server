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
    private $inStreamName;

    /**
     * @var resource
     */
    private $outStreamName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $inStream;

    /**
     * @var bool
     */
    private $outStream;

    public function __construct(LoggerInterface $logger, string $inStream = 'php://stdin', string $outStream = 'php://stdout')
    {
        $this->logger = $logger;

        $this->inStreamName = $inStream;
        $this->outStreamName = $outStream;
    }

    public function io(): IO
    {
        $this->logger->info('listening on stdio', [
            'in' => $inStream,
            'out' => $outStream
        ]);

        $this->inStream = fopen($this->inStreamName, 'r');
        $this->outStream = fopen($this->outStreamName, 'w');

        $this->validateStream($this->inStream);
        $this->validateStream($this->outStream);

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
        if (null === $stream || false === $stream) {
            throw new RuntimeException(sprintf(
                'Could not open stream'
            ));
        }
    }
}
