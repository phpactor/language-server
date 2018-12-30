<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;

interface StreamProvider
{
    public function provide(): Promise;
}
