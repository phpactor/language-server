<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Generator;
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

    public function dispatch(Handlers $handlers, RequestMessage $request): Generator
    {
        $handler = $handlers->get($request->method);

        $method = $this->methodResolver->resolveHandlerMethod($handler, $request->method);

        $arguments = $this->argumentResolver->resolveArguments(
            $handler,
            $method,
            $request->params
        );

        $messages = $handler->$method(...$arguments);

        if (null === $messages) {
            return;
        }

        if (!$messages instanceof Generator) {
            throw new RuntimeException(sprintf(
                '%s handler "%s" did not return a generator, it returned a: %s',
                $request->method,
                get_class($handler),
                is_object($messages) ? get_class($messages) : gettype($messages)
            ));
        }

        foreach ($messages as $message) {
            if ($message instanceof Message) {
                yield $message;
                continue;
            }
            yield new ResponseMessage($request->id, $message);
        }
    }
}
