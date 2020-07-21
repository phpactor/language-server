<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Psr\Log\NullLogger;
use RuntimeException;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class HandlerMethodRunner
{
    /**
     * @var Handlers
     */
    private $handlers;

    /**
     * @var HandlerMethodResolver
     */
    private $resolver;

    /**
     * @var array<string|int, CancellationTokenSource>
     */
    private $cancellations = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Handlers $handlers, HandlerMethodResolver $resolver, ?LoggerInterface $logger = null)
    {
        $this->handlers = $handlers;
        $this->resolver = $resolver;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $request): Promise
    {
        return \Amp\call(function () use ($request) {
            $handler = $this->handlers->get($request->method);
            $method = $this->resolver->resolveHandlerMethod($handler, $request->method);

            $cancellationTokenSource = new CancellationTokenSource();

            // we only cancel requests (that have IDs) and not notifications
            if ($request instanceof RequestMessage) {
                $this->cancellations[$request->id] = $cancellationTokenSource;
            }

            $promise = $handler->$method(
                $request->params,
                $cancellationTokenSource->getToken()
            ) ?? new Success(null);

            if (!$promise instanceof Promise) {
                throw new RuntimeException(sprintf(
                    'Handler "%s:%s" must return instance of Amp\\Promise, got "%s"',
                    get_class($handler),
                    $method,
                    is_object($promise) ? get_class($promise) : gettype($promise)
                ));
            }

            if (!$request instanceof RequestMessage) {
                return null;
            }

            return new ResponseMessage($request->id, yield $promise);
        });
    }

    public function cancelRequest(int $id): void
    {
        if (!isset($this->cancellations[$id])) {
            $this->logger->warning(sprintf(
                'Trying to cancel non-running request "%s", running requests: "%s"',
                $id,
                implode('", "', array_keys($this->cancellations))
            ));
            return;
        }

        $tokenSource = $this->cancellations[$id];
        assert($tokenSource instanceof CancellationTokenSource);
        $tokenSource->cancel();
    }
}
