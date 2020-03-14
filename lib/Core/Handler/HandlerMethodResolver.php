<?php

namespace Phpactor\LanguageServer\Core\Handler;

use RuntimeException;

use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class HandlerMethodResolver
{
    public function resolveHandlerMethod(Handler $handler, array $handlerMethods, $targetMethodName): string
    {
        if (!array_key_exists($targetMethodName, $handlerMethods)) {
            throw new RuntimeException(sprintf(
                'Resolved handler "%s" has not declared the method "%s", it declared "%s"',
                get_class($handler),
                $targetMethodName,
                implode('", "', $handlerMethods)
            ));
        }

        $method = $handlerMethods[$targetMethodName];

        if (!method_exists($handler, $method)) {
            throw new RuntimeException(sprintf(
                'Handler "%s" for method "%s" does not have the "%s" method defined, it has "%s"',
                get_class($handler),
                $targetMethodName,
                $method,
                implode('", "', get_class_methods($handler))
            ));
        }

        return $method;
    }
}
