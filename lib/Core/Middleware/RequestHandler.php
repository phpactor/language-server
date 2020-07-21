<?php

namespace Phpactor\LanguageServer\Core\Middleware;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;

final class RequestHandler
{
    /**
     * @var array<Middleware>
     */
    private $queue;

    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function handle(Message $request): ResponseMessage
    {
        $middleware = array_shift($this->queue);

        if (!$middleware) {
            throw new RuntimeException(
                'Middleware terminated (no middleware handled the request)'
            );
        }

        assert($middleware instanceof Middleware);

        return $middleware->process($request, $this);
    }
}
