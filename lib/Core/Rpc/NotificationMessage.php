<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use Phpactor\LanguageServer\Core\Rpc\Message;

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

    public function __construct(string $method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }
}
