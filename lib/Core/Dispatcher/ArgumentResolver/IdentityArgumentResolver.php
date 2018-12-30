<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

class IdentityArgumentResolver implements ArgumentResolver
{
    public function resolveArguments($object, string $method, array $arguments): array
    {
        return array_values($arguments);
    }
}
