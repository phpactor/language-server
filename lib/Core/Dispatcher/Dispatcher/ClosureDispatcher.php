<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Closure;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

final class ClosureDispatcher implements Dispatcher
{
    /**
     * @param Closure(Message): Promise<ResponseMessage|null> $closure
     */
    public function __construct(private Closure $closure)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(Message $request): Promise
    {
        /** @phpstan-ignore-next-line */
        return $this->closure->__invoke($request);
    }
}
