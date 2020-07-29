<?php

namespace Phpactor\LanguageServer\Core\Rpc;

final class ResponseError
{
    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param mixed $data
     */
    public function __construct(int $code, string $message, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}
