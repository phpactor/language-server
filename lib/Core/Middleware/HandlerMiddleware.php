<?php

namespace Phpactor\LanguageServer\Core\Middleware;

use Phpactor\LanguageServer\Core\Handler\HandlerMethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use function Amp\call;

class HandlerMiddleware implements Middleware
{
    /**
     * @var HandlerMethodDispatcher
     */
    private $dispatcher;

    public function __construct(HandlerMethodDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        if (!$request instanceof RequestMessage) {
            return $handler->handle($request, $handler);
        }

        return $this->dispatcher->dispatch($request);
    }
}
