<?php

namespace Phpactor\LanguageServer\Core;

interface Writer
{
    public function writeResponse(IO $io, $response): void;
}
