<?php

namespace Phpactor\LanguageServer\Core\Session;

use DateInterval;
use DateTimeImmutable;

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

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var DateTimeImmutable
     */
    private $created;

    public function __construct(string $rootUri, int $processId = null)
    {
        $this->rootUri = $rootUri;
        $this->processId = $processId;
        $this->workspace = new Workspace($rootUri);
        $this->created = new DateTimeImmutable();
    }

    public function processId(): ?int
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

    public function uptime(): DateInterval
    {
        return $this->created->diff(new DateTimeImmutable());
    }
}
