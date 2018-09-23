<?php

namespace Phpactor\LanguageServer\Adapter\DTL;

use DTL\ArgumentResolver\ArgumentResolver as UpstreamArgumentResolver;
use DTL\ArgumentResolver\ParamConverter\RecursiveInstantiator;
use Phpactor\LanguageServer\Core\ArgumentResolver;

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
        ]);
    }

    public function resolveArguments(string $class, string $method, array $arguments): array
    {
        return $this->dtlArgumnetResolver->resolveArguments($class, $method, $arguments);
    }
}
