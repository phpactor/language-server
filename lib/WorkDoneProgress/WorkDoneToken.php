<?php

namespace Phpactor\LanguageServer\WorkDoneProgress;

use Ramsey\Uuid\Uuid;

final class WorkDoneToken
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
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
