<?php

namespace LanguageServerProtocol;

class Unregistration
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $method;

    public function __construct(string $id, string $method)
    {
        $this->id = $id;
        $this->method = $method;
    }
}
