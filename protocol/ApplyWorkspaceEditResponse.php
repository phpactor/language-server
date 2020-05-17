<?php

namespace LanguageServerProtocol;

class ApplyWorkspaceEditResponse
{
    /**
     * @var bool
     */
    public $applied;

    /**
     * @var string|null
     */
    public $failureReason;

    public function __construct(bool $applied, ?string $failureReason = null)
    {
        $this->applied = $applied;
        $this->failureReason = $failureReason;
    }
}
