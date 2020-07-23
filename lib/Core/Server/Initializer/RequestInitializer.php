<?php

namespace Phpactor\LanguageServer\Core\Server\Initializer;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\Initializer;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;
use function Amp\call;

class RequestInitializer implements Initializer
{
    public function provideInitializeParams(Message $message): InitializeParams
    {
        if (!$request instanceof RequestMessage) {
            throw new RuntimeException(sprintf(
                'First request must be a RequestMessage (to initialize), got "%s"',
                get_class($request)
            ));
        }

        if (!$request->method === 'initialize') {
            throw new RuntimeException(sprintf(
                'First request must be an "initialize" request, got "%s"',
                $request->method
            ));
        }

        return InitializeParams::fromArray($request->params);
    }
}
