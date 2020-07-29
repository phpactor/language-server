<?php

namespace Phpactor\LanguageServer\Core\Rpc;

use DTL\Invoke\Invoke;
use Phpactor\LanguageServer\Core\Rpc\Exception\CouldNotCreateMessage;
use RuntimeException;

final class RequestMessageFactory
{
    public static function fromRequest(RawMessage $request): Message
    {
        try {
            return self::doFromRequest($request);
        } catch (RuntimeException $error) {
            throw new CouldNotCreateMessage($error->getMessage(), 0, $error);
        }
    }

    private static function doFromRequest(RawMessage $request): Message
    {
        $body = $request->body();
        unset($body['jsonrpc']);

        if (array_key_exists('result', $body)) {
            return Invoke::new(ResponseMessage::class, $body);
        }

        if (!isset($body['id']) || is_null($body['id'])) {
            unset($body['id']);
            return Invoke::new(NotificationMessage::class, $body);
        }

        return Invoke::new(RequestMessage::class, $body);
    }
}
