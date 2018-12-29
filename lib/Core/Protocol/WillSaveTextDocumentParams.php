<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;

class WillSaveTextDocumentParams
{
    /**
     * @var TextDocumentIdentifier
     */
    private $identifier;

    /**
     * @var int
     */
    private $reason;

    public function __construct(TextDocumentIdentifier $identifier, int $reason)
    {
        $this->identifier = $identifier;
        $this->reason = $reason;
    }
    
}
