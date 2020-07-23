<?php

namespace Phpactor\LanguageServer\Core\Server;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;

interface Initializer
{
    /**
     * Provide initialization parameters.
     *
     * Typically implementations will be passed the _first request_ to the
     * language server session, which should be the initialization request from
     * which the initialize parameters can be extracted (including the
     * ClientCapabilities).
     */
    public function provideInitializeParams(Message $message): InitializeParams;
}
