<?php

namespace Phpactor\LanguageServer\Core\Server\Recorder;

interface Recorder
{
    public function write(string $chunk);

    public function shutdown();
}
