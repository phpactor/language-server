<?php

namespace Phpactor\LanguageServer\Core\Connection;

use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\IO\StreamIO;

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

    public function __construct(string $inStream = 'php://stdin', string $outStream = 'php://stdout')
    {
        $this->inStream = fopen($inStream, 'r');
        $this->outStream = fopen($outStream, 'w');
    }

    public function io(): IO
    {
        return new StreamIO($this->inStream, $this->outStream);
    }

    public function __destruct()
    {
        fclose($this->inStream);
        fclose($this->outStream);
    }
}
