<?php

namespace Phpactor\LanguageServer\Core\Server\Recorder;

class NullRecorder implements Recorder
{
    public function write(string $chunk)
    {
    }

    public function shutdown()
    {
    }
}
