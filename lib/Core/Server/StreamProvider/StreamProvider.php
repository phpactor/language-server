<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;

interface StreamProvider
{
    public function accept(): Promise;

    public function close(): void;
}
