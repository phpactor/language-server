<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;

class ResponseMessage extends Message
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var object
     */
    public $result;

    /**
     * @var ResponseError|null
     */
    public $responseError;

    public function __construct(int $id, $result, ?ResponseError $responseError = null)
    {
        $this->id = $id;
        $this->result = $result;
        $this->responseError = $responseError;
    }
}
