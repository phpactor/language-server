<?php

namespace Phpactor\LanguageServer\Core;

class Session
{
    /**
     * @var string
     */
    private $rootUri;

    /**
     * @var int|null
     */
    private $processId;

    public function initialize(string $rootUri, ?int $processId = null)
    {
        $this->rootUri = $rootUri;
        $this->processId = $processId;
    }

    public function rootUri(): string
    {
        return $this->rootUri;
    }

    public function processId(): ?int
    {
        return $this->processId;
    }
}
