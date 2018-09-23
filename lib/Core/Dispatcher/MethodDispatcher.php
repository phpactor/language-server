<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

class MethodDispatcher implements Dispatcher
{
    /**
     * @var Handlers
     */
    private $handlers;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    public function __construct(ArgumentResolver $argumentResolver, Handlers $handlers)
    {
        $this->handlers = $handlers;
        $this->argumentResolver = $argumentResolver;
    }

    public function dispatch(RequestMessage $request): ResponseMessage
    {
        $handler = $this->handlers->get($request->method);

        $arguments = $this->argumentResolver->resolveArguments(
            get_class($handler),
            '__invoke',
            $request->params
        );

        $result = $handler->__invoke(...$arguments);

        return new ResponseMessage($request->id, $result);
    }
}
