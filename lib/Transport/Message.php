<?php

namespace Phpactor\LanguageServer\Transport;

abstract class Message
{
    /**
     * @var string
     */
    public $jsonRpc = '2.0';
}
