<?php

namespace Phpactor\LanguageServer\Core;

class Chunk
{
    /**
     * @var string|null
     */
    private $contents;

    public function __construct(?string $contents = null)
    {
        $this->contents = $contents;
    }

    public function hasContents()
    {
        return null !== $this->contents;
    }

    public function contents(): ?string
    {
        return $this->contents;
    }
}
