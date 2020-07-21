<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\Promise;
use Closure;

class ClosureHandler implements Handler
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var Closure
     */
    private $closure;

    public function __construct(string $methodName, Closure $closure)
    {
        $this->methodName = $methodName;
        $this->closure = $closure;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            $this->methodName => 'handle'
        ];
    }

    public function handle(array $params)
    {
        return $this->closure->__invoke($params);
    }
}
