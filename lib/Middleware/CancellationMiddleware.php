<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Rpc\Message;

class CancellationMiddleware implements Middleware
{
    const METHOD_CANCEL_REQUEST = '$/cancelRequest';

    /**
     * @var HandlerMethodRunner
     */
    private $runner;

    public function __construct(HandlerMethodRunner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Message $message, RequestHandler $handler): Promise
    {
        if ($message instanceof NotificationMessage) {
            if ($message->method === self::METHOD_CANCEL_REQUEST) {
                $this->runner->cancelRequest($message);
                return new Success(null);
            }
        }

        return $handler->handle($message, $handler);
    }
}
