<?php

namespace Phpactor\LanguageServer\Core\Transport;

use Phpactor\LanguageServer\Core\Exception\ServerError;

class RequestMessageFactory
{
    public function requestMessageFromArray(array $array): RequestMessage
    {
        $keys = [ 'jsonrpc', 'id', 'method', 'params' ];

        if ($diff = array_diff(array_keys($array), $keys)) {
            throw new ServerError(sprintf(
                'Request has invalid keys: "%s", valid keys: "%s"',
                implode(', ', $diff),
                implode(', ', $keys)
            ));
        }

        $array = array_merge([
            'id' => null
        ], $array);

        if ($diff = array_diff($keys, array_keys($array))) {
            throw new ServerError(sprintf(
                'Request is missing required keys: "%s"',
                implode(', ', $diff)
            ));
        }

        return new RequestMessage((int) $array['id'], $array['method'], $array['params']);
    }
}
