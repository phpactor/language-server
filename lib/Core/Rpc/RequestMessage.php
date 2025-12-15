<?php

namespace Phpactor\LanguageServer\Core\Rpc;

final class RequestMessage extends Message
{
    /**
     * @param string|int $id
     */
    public function __construct(
        public $id,
        public string $method,
        /** @var array<string,mixed>|null */
        public ?array $params
    ) {
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
