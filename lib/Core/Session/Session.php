<?php

namespace Phpactor\LanguageServer\Core\Session;

use DateInterval;
use DateTimeImmutable;
use Psr\Container\ContainerInterface;

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

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        string $rootUri,
        int $processId = null,
        ContainerInterface $container = null
    ) {
        $this->rootUri = $rootUri;
        $this->processId = $processId;
        $this->workspace = new Workspace();
        $this->created = new DateTimeImmutable();
        $this->container = $container ?: new NullContainer();
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

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
