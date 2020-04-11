<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Psr\Log\LoggerInterface;

class CancellingMethodDispatcher implements Dispatcher
{
    const METHOD_CANCEL_REQUEST = '$/cancelRequest';

    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    /**
     * @var array<int, CancellationTokenSource>
     */
    private $cancellations = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Dispatcher $innerDispatcher, LoggerInterface $logger)
    {
        $this->innerDispatcher = $innerDispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(
        Handlers $handlers,
        Message $request,
        array $extraArgs
    ): Promise {
        return \Amp\call(function () use ($handlers, $request, $extraArgs) {
            if ($request->method === self::METHOD_CANCEL_REQUEST) {
                $this->cancelRequest($request);
                return null;
            }

            $cancellationTokenSource = new CancellationTokenSource();

            // we only cancel requests (that have IDs) and not notifications
            if ($request instanceof RequestMessage) {
                $this->cancellations[$request->id] = $cancellationTokenSource;
            }

            $extraArgs['_cancel'] = $cancellationTokenSource->getToken();

            $response = yield $this->innerDispatcher->dispatch($handlers, $request, $extraArgs);

            return $response;
        });
    }

    private function cancelRequest(Message $request): void
    {
        $id = $request->params['id'];
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

    private function createCancellationMessage(int $id, string $message): ResponseMessage
    {
        return new ResponseMessage(
            $id,
            null,
            new ResponseError(
                ErrorCodes::RequestCancelled,
                $message
            )
        );
    }
}
