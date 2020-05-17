<?php

namespace LanguageServerProtocol;

class ApplyWorkspaceEditResponse
{
    /**
     * @var bool
     */
    public $applied;

    /**
     * @var string
     */
    public $failureReason;

    public function __construct(bool $applied, string $failureReason)
    {
        $this->applied = $applied;
        $this->failureReason = $failureReason;
    }
}
