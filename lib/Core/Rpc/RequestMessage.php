<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class RequestMessage extends Message
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

    public function __construct(int $id, string $method, array $params)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params,
        ];
    }
}
