<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;
use PHPUnit\Framework\MockObject\Generator;

interface StreamProvider
{
    public function provide(): Promise;
}
