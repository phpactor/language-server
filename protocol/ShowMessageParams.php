<?php

namespace LanguageServerProtocol;

class ShowMessageParams
{
    /**
     * See {@link MessageType}
     *
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $message;

    public function __construct(int $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
}
