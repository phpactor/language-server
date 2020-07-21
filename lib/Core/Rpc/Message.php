<?php

namespace Phpactor\LanguageServer\Core\Rpc;

abstract class Message
{
    /**
     * @var string
     */
    public $jsonrpc = '2.0';
}
