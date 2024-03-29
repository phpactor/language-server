<?php

namespace Phpactor\LanguageServer\Core\Rpc;

final class RequestMessage extends Message
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array<string,mixed>|null
     */
    public $params;


    /**
     * @param string|int $id
     */
    public function __construct($id, string $method, ?array $params)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params,
        ];
    }
}
