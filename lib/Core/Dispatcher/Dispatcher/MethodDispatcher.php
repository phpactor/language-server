<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

class MethodDispatcher implements Dispatcher
{
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var HandlerMethodResolver
     */
    private $methodResolver;


    public function __construct(ArgumentResolver $argumentResolver, ?HandlerMethodResolver $resolver = null)
    {
        $this->argumentResolver = $argumentResolver;
        $this->methodResolver = $resolver ?: new HandlerMethodResolver();
    }

    public function dispatch(Handlers $handlers, RequestMessage $request, array $extraArgs): Promise
    {
        return \Amp\call(function () use ($request, $handlers) {
            $handler = $handlers->get($request->method);

            $method = $this->methodResolver->resolveHandlerMethod($handler, $request->method);

            $arguments = $this->argumentResolver->resolveArguments(
                $handler,
                $method,
                $request->params
            );

            $promise = $handler->$method(...$arguments) ?? new Success(null);
            if (!$promise instanceof Promise) {
                throw new RuntimeException(sprintf(
                    'Handler "%s:%s" must return instance of Promise, got "%s"',
                    get_class($handler),
                    $method,
                    is_object($promise) ? get_class($promise) : gettype($promise)
                ));
            }
            $result = yield $promise;

            if (null === $result) {
                return;
            }

            if ($result instanceof Message) {
                return $result;
            }

            return new ResponseMessage($request->id, $result);
        });
    }
}
