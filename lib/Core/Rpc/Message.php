<?php

namespace Phpactor\LanguageServer\Core\Rpc;

abstract class Message
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params;

    /**
     * @var string
     */
    public $jsonrpc = '2.0';
}
