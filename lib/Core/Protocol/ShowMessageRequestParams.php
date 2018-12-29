<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use LanguageServerProtocol\MessageActionItem;

class ShowMessageRequestParams extends ShowMessageParams
{
    /**
     * @var MessageActionItem[]
     */
    public $actions;

    public function __construct(int $type, string $message, ?array $actions = [])
    {
        parent::__construct($type, $message);
        $this->actions = $actions;
    }
}
