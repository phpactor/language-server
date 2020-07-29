<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

final class PassThroughArgumentResolver implements ArgumentResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolveArguments(object $object, string $method, array $arguments): array
    {
        return $arguments;
    }
}
