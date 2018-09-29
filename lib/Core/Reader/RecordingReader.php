<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Reader;
use Phpactor\LanguageServer\Core\Transport\Request;
use RuntimeException;

class RecordingReader implements Reader
{
    /**
     * @var Reader
     */
    private $innerReader;

    /**
     * @var resource
     */
    private $recordStream;

    public function __construct(Reader $innerReader, string $recordPath)
    {
        $this->innerReader = $innerReader;
        $stream = fopen($recordPath, 'w');

        if (false === $stream) {
            throw new RuntimeException(sprintf(
                'Could not open stream "%s"',
                $recordPath
            ));
        }

        $this->recordStream = $stream;
    }

    public function readRequest(IO $io): Request
    {
        $request = $this->innerReader->readRequest($io);
        fwrite($this->recordStream, $request->body() . PHP_EOL);
        return $request;
    }

    public function __destruct()
    {
        fclose($this->recordStream);
    }
}
