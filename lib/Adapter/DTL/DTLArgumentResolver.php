<?php

namespace Phpactor\LanguageServer\Adapter\DTL;

use DTL\ArgumentResolver\ArgumentResolver as InnerDTLArgumentResolver;
use Phpactor\LanguageServer\Core\ArgumentResolver;

class DTLArgumentResolver implements ArgumentResolver
{
    /**
     * @var ArgumentResolver
     */
    private $dtlArgumnetResolver;

    public function __construct(InnerDTLArgumentResolver $dtlArgumnetResolver = null)
    {
        $this->dtlArgumnetResolver = $dtlArgumnetResolver ?: new InnerDTLArgumentResolver;
    }

    public function resolveArguments(string $class, string $method, array $arguments): array
    {
        return $this->dtlArgumnetResolver->resolveArguments($class, $method, $arguments);
    }
}
