<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Closure;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Rpc\Message;

final class ClosureDispatcher implements Dispatcher
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(Message $request): Promise
    {
        return $this->closure->__invoke($request);
    }
}
