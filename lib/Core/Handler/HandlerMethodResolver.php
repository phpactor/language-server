<?php

namespace Phpactor\LanguageServer\Core\Handler;

use RuntimeException;

final class HandlerMethodResolver
{
    public function resolveHandlerMethod(Handler $handler, string $languageServerMethod): string
    {
        $handlerMap = $handler->methods();

        if (!array_key_exists($languageServerMethod, $handlerMap)) {
            throw new RuntimeException(sprintf(
                'Resolved handler "%s" has not declared support for LSP method "%s", it declared support for "%s"',
                get_class($handler),
                $languageServerMethod,
                implode('", "', $handlerMap)
            ));
        }

        $method = $handlerMap[$languageServerMethod];

        if (!method_exists($handler, $method)) {
            throw new RuntimeException(sprintf(
                'Handler "%s" for method "%s" does not have the "%s" method defined, it has "%s"',
                get_class($handler),
                $languageServerMethod,
                $method,
                implode('", "', get_class_methods($handler))
            ));
        }

        return $method;
    }
}
