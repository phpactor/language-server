<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

interface ArgumentResolver
{
    public function resolveArguments($object, string $method, array $arguments): array;
}
