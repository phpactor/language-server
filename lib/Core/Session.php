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

    public function __construct(string $rootUri, int $processId = null)
    {
        $this->rootUri = $rootUri ?: getcwd();
        $this->processId = $processId ?: getmypid();
        $this->workspace = new Workspace($rootUri);
    }

    public function processId(): int
    {
        return $this->processId;
    }

    public function rootUri(): string
    {
        return $this->rootUri;
    }

    public function workspace(): Workspace
    {
        return $this->workspace;
    }
}
