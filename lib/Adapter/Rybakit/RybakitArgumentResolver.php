<?php

namespace Phpactor\LanguageServer\Adapter\Rybakit;

use ArgumentsResolver\InDepthArgumentsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

class RybakitArgumentResolver implements ArgumentResolver
{
    public function resolveArguments(object $object, string $method, array $arguments): array
    {
        $resolver = new InDepthArgumentsResolver([$object, $method]);
        return $resolver->resolve($arguments);
    }
}
