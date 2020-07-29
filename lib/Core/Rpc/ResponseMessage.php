<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use JsonSerializable;

final class ResponseMessage extends Message implements JsonSerializable
{
    /**
     * @var int|string
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
        $this->id = $id;
        $this->result = $result;
        $this->error = $error;
    }

    public function jsonSerialize(): array
    {
        $response = [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id,
            'result' => $this->result,
        ];

        if (null !== $this->error) {
            $response['error'] = $this->error;
        }

        return $response;
    }
}
