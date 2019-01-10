<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

interface StreamParser
{
    public function feed(string $chunk): void;
}
