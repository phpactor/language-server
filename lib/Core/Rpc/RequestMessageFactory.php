<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use RuntimeException;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;

class RequestMessageFactory
{
    public static function fromRequest(RawMessage $request): RequestMessage
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

        $id = $array['id'];
        if (!is_null($id)) {
            $id = (int)$id;
        }

        return new RequestMessage($id, $array['method'], $array['params'] ?? []);
    }
}
