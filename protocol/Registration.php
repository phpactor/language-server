<?php

namespace LanguageServerProtocol;

class Registration
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $method;

    public $registerOptions;

    public function __construct(string $id, string $method, $registerOptions = null)
    {
        $this->id = $id;
        $this->method = $method;
        $this->registerOptions = $registerOptions;
    }
}
