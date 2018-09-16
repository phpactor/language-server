<?php

namespace Phpactor\LanguageServer\Core\Transport;

use Phpactor\LanguageServer\Core\Transport\Message;

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
}
