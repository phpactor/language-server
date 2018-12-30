<?php

namespace Phpactor\LanguageServer\Adapter\DTL;

use DTL\ArgumentResolver\ArgumentResolver as UpstreamArgumentResolver;
use DTL\ArgumentResolver\ParamConverter\RecursiveInstantiator;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;

class DTLArgumentResolver implements ArgumentResolver
{
    /**
     * @var UpstreamArgumentResolver
     */
    private $dtlArgumnetResolver;

    public function __construct(UpstreamArgumentResolver $dtlArgumnetResolver = null)
    {
        $this->dtlArgumnetResolver = $dtlArgumnetResolver ?: new UpstreamArgumentResolver([
            new RecursiveInstantiator()
        ], UpstreamArgumentResolver::ALLOW_UNKNOWN_ARGUMENTS);
    }

    public function resolveArguments($object, string $method, array $arguments): array
    {
        return $this->dtlArgumnetResolver->resolveArguments(get_class($object), $method, $arguments);
    }
}
