<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use JsonSerializable;

class ResponseMessage extends Message implements JsonSerializable
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
        $this->id = (int)$id;
        $this->result = $result;
        $this->error = $error;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'result' => $this->result,
        ];
    }
}
