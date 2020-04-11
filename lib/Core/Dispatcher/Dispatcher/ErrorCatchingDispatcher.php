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
use Throwable;

class ErrorCatchingDispatcher implements Dispatcher
{
    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    public function __construct(Dispatcher $innerDispatcher)
    {
        $this->innerDispatcher = $innerDispatcher;
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
                    // how to handle this?
                    throw $error;
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
