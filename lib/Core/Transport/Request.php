<?php

namespace Phpactor\LanguageServer\Core\Transport;

class Request
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    public function __construct(array $headers, string $body)
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
