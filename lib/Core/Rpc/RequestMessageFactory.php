<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use RuntimeException;

class RequestMessageFactory
{
    public static function fromRequest(Request $request): RequestMessage
    {
        $array = $request->body();

        $keys = [ 'jsonrpc', 'id', 'method', 'params' ];

        if ($diff = array_diff(array_keys($array), $keys)) {
            throw new RuntimeException(sprintf(
                'Request has invalid keys: "%s", valid keys: "%s"',
                implode('", "', $diff),
                implode('", "', $keys)
            ));
        }

        $array = array_merge([
            'id' => null
        ], $array);

        if ($diff = array_diff($keys, array_keys($array))) {
            throw new RuntimeException(sprintf(
                'Request is missing required keys: "%s"',
                implode(', ', $diff)
            ));
        }

        return new RequestMessage((int) $array['id'], $array['method'], $array['params'] ?? []);
    }
}
