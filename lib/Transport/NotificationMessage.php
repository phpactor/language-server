<?php

namespace Phpactor\LanguageServer\Transport;

class NotificationMessage
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
