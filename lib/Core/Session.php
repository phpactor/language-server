<?php

namespace Phpactor\LanguageServer\Core;

class Session
{
    /**
     * @var string
     */
    private $rootUri;

    /**
     * @var int
     */
    private $processId;

    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(string $rootUri, ?int $processId, Workspace $workspace)
    {
        $this->rootUri = $rootUri;
        $this->processId = $processId;
        $this->workspace = $workspace;
    }
}
