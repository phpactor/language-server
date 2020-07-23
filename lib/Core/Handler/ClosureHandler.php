<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\CancellationToken;
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

    public function handle(array $params, CancellationToken $token = null)
    {
        return $this->closure->__invoke($params, $token);
    }
}
