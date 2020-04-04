<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;

interface StreamProvider
{
    /**
     * @return Promise<Connection|null>
     */
    public function accept(): Promise;

    public function close(): void;
}
