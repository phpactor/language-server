<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;

interface RequestReader
{
    /**
     * @return Promise<RawMessage|null>
     */
    public function wait(): Promise;
}
