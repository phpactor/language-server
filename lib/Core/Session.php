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

    public function __construct(string $rootUri, int $processId = null)
    {
        $this->rootUri = $rootUri ?: getcwd();
        $this->processId = $processId ?: getmypid();
    }

    public function processId(): int
    {
        return $this->processId;
    }

    public function rootUri(): string
    {
        return $this->rootUri;
    }
}
