<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class Request
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $body;

    public function __construct(array $headers, array $body)
    {
        $this->headers = $headers;
        $this->body = $body;
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
