<?php

namespace Phpactor\LanguageServer\Core\Transport;

abstract class Message
{
    /**
     * @var string
     */
    public $jsonRpc = '2.0';
}
