<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Ramsey\Uuid\Uuid;

final class WorkDoneToken
{
    public function __construct(private string $token)
    {
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public static function generate(): self
    {
        return new self((string) Uuid::uuid4());
    }
}
