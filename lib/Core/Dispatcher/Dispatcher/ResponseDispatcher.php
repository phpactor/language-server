<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;

class ResponseDispatcher implements Dispatcher
{
    /**
     * @var ResponseWatcher
     */
    private $watcher;

    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    public function __construct(Dispatcher $innerDispatcher, ResponseWatcher $watcher)
    {
        $this->watcher = $watcher;
        $this->innerDispatcher = $innerDispatcher;
    }

    public function dispatch(Handlers $handlers, Message $request, array $extraArgs): Promise
    {
        if ($request instanceof ResponseMessage) {
            $this->watcher->handle($request);
            return new Success(null);
        }

        return $this->innerDispatcher->dispatch($handlers, $request, $extraArgs);
    }
}
