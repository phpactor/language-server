<?php

namespace Phpactor\LanguageServer\Core;

interface Connection
{
    public function io(): IO;

    public function shutdown();

    public function reset(): void;
}
