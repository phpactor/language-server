<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Exception\ServerControl;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorCatchingDispatcher implements Dispatcher
{
    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Dispatcher $innerDispatcher, LoggerInterface $logger)
    {
        $this->innerDispatcher = $innerDispatcher;
        $this->logger = $logger;
    }

    public function dispatch(Handlers $handlers, Message $request, array $extraArgs): Promise
    {
        return \Amp\call(function () use ($handlers, $request, $extraArgs) {
            try {
                return yield $this->innerDispatcher->dispatch($handlers, $request, $extraArgs);
            } catch (ServerControl $exception) {
                throw $exception;
            } catch (Throwable $error) {
                if (!$request instanceof RequestMessage) {
                    $this->logger->error(sprintf(
                        'Error when handling "%s" (%s): %s',
                        get_class($request),
                        json_encode($request),
                        $error->getMessage()
                    ));
                    return null;
                }

                return new Success(new ResponseMessage($request->id, null, new ResponseError(
                    $this->resolveErrorCode($error),
                    $error->getMessage(),
                    $error->getTraceAsString()
                )));
            }
        });
    }

    private function resolveErrorCode(Throwable $error): int
    {
        if ($error instanceof HandlerNotFound) {
            return ErrorCodes::MethodNotFound;
        }

        return ErrorCodes::InternalError;
    }
}
