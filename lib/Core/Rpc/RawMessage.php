<?php

namespace Phpactor\LanguageServer\Core\Rpc;

final class RawMessage
{
    public function __construct(private array $headers, private array $body)
    {
    }

    public function body(): array
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
