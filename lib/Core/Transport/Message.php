<?php

namespace Phpactor\LanguageServer\Core\Transport;

abstract class Message
{
    /**
     * @var string
     */
    public $jsonrpc = '2.0';
}
