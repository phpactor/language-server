<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class RequestMessage extends Message
{
    /**
     * @var string
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

    /**
     * @param string|int $id
     */
    public function __construct($id, string $method, ?array $params)
    {
        $this->id = (string)$id;
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
