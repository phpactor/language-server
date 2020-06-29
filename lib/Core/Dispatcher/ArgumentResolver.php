<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

interface ArgumentResolver
{
    public function resolveArguments(object $object, string $method, array $arguments, array $extraArgs): array;
}
