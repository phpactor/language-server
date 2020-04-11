<?php

namespace Phpactor\LanguageServer\Core\Rpc;

class ResponseMessage extends Message
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var mixed
     */
    public $result;

    /**
     * @var ResponseError|null
     */
    public $error;

    /**
     * @param mixed $result
     * @param string|int $id
     */
    public function __construct($id, $result, ?ResponseError $error = null)
    {
        $this->id = (string)$id;
        $this->result = $result;
        $this->error = $error;
    }
}
