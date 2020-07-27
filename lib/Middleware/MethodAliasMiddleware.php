<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class MethodAliasMiddleware implements Middleware
{
    /**
     * @var array
     */
    private $aliasMap;

    public function __construct(array $aliasMap)
    {
        $this->aliasMap = $aliasMap;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        if (!$request instanceof RequestMessage && !$request instanceof NotificationMessage) {
            return $handler->handle($request);
        }

        if (isset($this->aliasMap[$request->method])) {
            $request->method = $this->aliasMap[$request->method];
        }

        return $handler->handle($request);
    }
}
