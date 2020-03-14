<?php

namespace Phpactor\LanguageServer\Core\Handler;

use RuntimeException;

use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class HandlerMethodResolver
{
    public function resolveHandlerMethod(Handler $handler, RequestMessage $request): string
    {
        $handlerMethods = $handler->methods();

        if (!array_key_exists($request->method, $handlerMethods)) {
            throw new RuntimeException(sprintf(
                'Resolved handler "%s" has not declared the method "%s"',
                get_class($handler),
                $request->method
            ));
        }

        $method = $handlerMethods[$request->method];

        if (!method_exists($handler, $method)) {
            throw new RuntimeException(sprintf(
                'Handler "%s" for method "%s" does not have the "%s" method defined, it has "%s"',
                get_class($handler),
                $request->method,
                $method,
                implode('", "', get_class_methods($handler))
            ));
        }

        return $method;
    }
}
