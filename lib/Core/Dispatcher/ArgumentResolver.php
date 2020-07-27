<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;

interface ArgumentResolver
{
    /**
     * @throws CouldNotResolveArguments
     */
    public function resolveArguments(object $object, string $method, array $arguments): array;
}
