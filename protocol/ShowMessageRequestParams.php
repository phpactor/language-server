<?php

namespace LanguageServerProtocol;

class ShowMessageRequestParams extends ShowMessageParams
{
    /**
     * @var MessageActionItem[]
     */
    public $actions;

    /**
     * @param MessageActionItem[] $actions
     */
    public function __construct(int $type, string $message, ?array $actions = [])
    {
        parent::__construct($type, $message);
        $this->actions = $actions;
    }
}
