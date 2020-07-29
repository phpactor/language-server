<?php

namespace Phpactor\LanguageServer\Core\Server\Initializer;

use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Initializer;
use RuntimeException;

final class RequestInitializer implements Initializer
{
    public function provideInitializeParams(Message $request): InitializeParams
    {
        if (!$request instanceof RequestMessage) {
            throw new RuntimeException(sprintf(
                'First request must be a RequestMessage (to initialize), got "%s"',
                get_class($request)
            ));
        }

        if ($request->method !== 'initialize') {
            throw new RuntimeException(sprintf(
                'Method of first request must be "initialize", got "%s"',
                $request->method
            ));
        }

        return InitializeParams::fromArray($request->params, true);
    }
}
