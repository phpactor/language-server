<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Reader;
use Phpactor\LanguageServer\Core\Transport\Request;

class RecordingReader implements Reader
{
    /**
     * @var Reader
     */
    private $innerReader;

    /**
     * @var bool
     */
    private $recordStream;

    public function __construct(Reader $innerReader, string $recordPath)
    {
        $this->innerReader = $innerReader;
        $this->recordStream = fopen($recordPath, 'w');
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
