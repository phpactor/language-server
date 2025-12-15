<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Closure;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;

class ClosureMiddleware implements Middleware
{
    public function __construct(private Closure $closure)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $request, RequestHandler $handler): Promise
    {
        /** @phpstan-ignore-next-line */
        return $this->closure->__invoke($request, $handler);
    }
}
