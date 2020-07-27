<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;

class ResponseHandlingMiddleware implements Middleware
{
    /**
     * @var ResponseWatcher
     */
    private $watcher;

    public function __construct(ResponseWatcher $watcher)
    {
        $this->watcher = $watcher;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        if ($request instanceof ResponseMessage) {
            $this->watcher->handle($request);
            return new Success(null);
        }

        return $handler->handle($request);
    }
}
