<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use RuntimeException;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class HandlerMethodDispatcher
{
    /**
     * @var Handlers
     */
    private $handlers;

    /**
     * @var HandlerMethodResolver
     */
    private $resolver;

    public function __construct(Handlers $handlers, HandlerMethodResolver $resolver)
    {
        $this->handlers = $handlers;
        $this->resolver = $resolver;
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $request): Promise
    {
        return \Amp\call(function () use ($request) {
            $handler = $this->handlers->get($request->method);
            $method = $this->resolver->resolveHandlerMethod($handler, $request->method);
            $promise = $handler->$method($request->params) ?? new Success(null);

            if (!$promise instanceof Promise) {
                throw new RuntimeException(sprintf(
                    'Handler "%s:%s" must return instance of Amp\\Promise, got "%s"',
                    get_class($handler),
                    $method,
                    is_object($promise) ? get_class($promise) : gettype($promise)
                ));
            }

            if (!$request instanceof RequestMessage) {
                return null;
            }

            return new ResponseMessage($request->id, yield $promise);
        });
    }
}
