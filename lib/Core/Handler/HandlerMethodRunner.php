<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

final class HandlerMethodRunner implements MethodRunner
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

    /**
     * @var ArgumentResolver|null
     */
    private $argumentResolver;

    public function __construct(
        Handlers $handlers,
        ?ArgumentResolver $argumentResolver = null,
        ?LoggerInterface $logger = null,
        ?HandlerMethodResolver $resolver = null
    ) {
        $this->handlers = $handlers;
        $this->resolver = $resolver ?: new HandlerMethodResolver();
        $this->logger = $logger ?: new NullLogger();
        $this->argumentResolver = $argumentResolver ?: new PassThroughArgumentResolver();
    }

    /**
     * @return Promise<ResponseMessage|null>
     */
    public function dispatch(Message $request): Promise
    {
        if (
            !$request instanceof NotificationMessage &&
            !$request instanceof RequestMessage
        ) {
            throw new RuntimeException(sprintf(
                'Message must either be a Notification or a Request, got "%s"',
                get_class($request)
            ));
        }

        return \Amp\call(function () use ($request) {
            $handler = $this->handlers->get($request->method);
            $method = $this->resolver->resolveHandlerMethod($handler, $request->method);

            $cancellationTokenSource = new CancellationTokenSource();

            // we only cancel requests (that have IDs) and not notifications
            if ($request instanceof RequestMessage) {
                $this->cancellations[$request->id] = $cancellationTokenSource;
            }

            $args = array_values($this->argumentResolver->resolveArguments($handler, $method, $request->params ?? []));
            $args[] = $cancellationTokenSource->getToken();

            $promise = $handler->$method(...$args) ?? new Success(null);

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

    /**
     * @param string|int $id
     */
    public function cancelRequest($id): void
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
