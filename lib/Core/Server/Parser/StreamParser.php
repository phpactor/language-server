<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Amp\Promise;

interface StreamParser
{
    public function wait(): Promise;
}
