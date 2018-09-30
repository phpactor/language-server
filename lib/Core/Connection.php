<?php

namespace Phpactor\LanguageServer\Core;

interface Connection
{
    public function accept(): IO;

    public function shutdown();

    public function reset(): void;
}
