<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class NotificationMessage extends Message
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

    public function __construct(string $method, ?array $params = null)
    {
        $this->method = $method;
        $this->params = $params;
    }
}
