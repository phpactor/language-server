<?php

namespace Phpactor\LanguageServer\Core\ArgumentResolver;

use Phpactor\LanguageServer\Core\ArgumentResolver;

class IdentityArgumentResolver implements ArgumentResolver
{
    public function resolveArguments(string $class, string $method, array $arguments): array
    {
        return array_values($arguments);
    }
}
