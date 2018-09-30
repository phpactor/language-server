<?php

namespace Phpactor\LanguageServer\Core;

interface ArgumentResolver
{
    public function resolveArguments($object, string $method, array $arguments): array;
}
