<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use JsonSerializable;

final class ResponseMessage extends Message implements JsonSerializable
{
    /**
     * @param mixed $result
     * @param string|int $id
     */
    public function __construct(public $id, public $result, public ?ResponseError $error = null)
    {
    }

    public function jsonSerialize(): array
    {
        $response = [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id
        ];

        if (null !== $this->error) {
            $response['error'] = $this->error;
        } else {
            $response['result'] = $this->result;
        }

        return $response;
    }
}
