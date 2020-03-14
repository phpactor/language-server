<?php

namespace Phpactor\LanguageServer\Core\Server\Recorder;

use Amp\ByteStream\OutputStream;

class StreamRecorder implements Recorder
{
    /**
     * @var OutputStream
     */
    private $outputStream;

    public function __construct(OutputStream $outputStream)
    {
        $this->outputStream = $outputStream;
    }

    public function write(string $chunk)
    {
        $this->outputStream->write($chunk);
    }

    public function shutdown()
    {
        $this->outputStream->end();
    }
}
