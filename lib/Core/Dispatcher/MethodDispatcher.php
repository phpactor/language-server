<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Generator;
use Phpactor\LanguageServer\Core\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\Message;
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

    public function dispatch(RequestMessage $request): Generator
    {
        $handler = $this->handlers->get($request->method);

        $arguments = $this->argumentResolver->resolveArguments(
            get_class($handler),
            '__invoke',
            $request->params
        );

        $generator = $handler->__invoke(...$arguments);

        foreach ($generator as $response) {
            if ($response instanceof Message) {
                yield $response;
                continue;
            }
            yield new ResponseMessage($request->id, $response);
        }
    }
}
