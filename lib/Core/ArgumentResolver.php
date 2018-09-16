<?php

namespace Phpactor\LanguageServer\Core;

interface ArgumentResolver
{
    public function resolveArguments(string $class, string $method, array $arguments): array;
}
