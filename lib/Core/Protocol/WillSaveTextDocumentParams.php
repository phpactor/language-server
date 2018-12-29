<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;

class WillSaveTextDocumentParams
{
    /**
     * @var TextDocumentIdentifier
     */
    public $identifier;

    /**
     * @var int
     */
    public $reason;

    public function __construct(TextDocumentIdentifier $identifier, int $reason)
    {
        $this->identifier = $identifier;
        $this->reason = $reason;
    }
    
}
