<?php

namespace Phpactor\LanguageServer\Core\Handler;

use RuntimeException;

final class HandlerMethodResolver
{
    public function resolveHandlerMethod(Handler $handler, array $handlerMap, $targetMethodName): string
    {
        if (!array_key_exists($targetMethodName, $handlerMap)) {
            throw new RuntimeException(sprintf(
                'Resolved handler "%s" has not declared the method "%s", it declared "%s"',
                get_class($handler),
                $targetMethodName,
                implode('", "', $handlerMap)
            ));
        }

        $method = $handlerMap[$targetMethodName];

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
