<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\MethodRunner;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class HandlerMiddleware implements Middleware
{
    /**
     * @var MethodRunner
     */
    private $runner;

    public function __construct(MethodRunner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        if (
            !$request instanceof RequestMessage &&
            !$request instanceof NotificationMessage
        ) {
            return $handler->handle($request);
        }

        return $this->runner->dispatch($request);
    }
}
