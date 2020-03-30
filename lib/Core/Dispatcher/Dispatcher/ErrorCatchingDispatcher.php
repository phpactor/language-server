<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
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

    public function dispatch(Handlers $handlers, RequestMessage $request): Generator
    {
        try {
            yield from $this->innerDispatcher->dispatch($handlers, $request);
        } catch (ServerControl $exception) {
            throw $exception;
        } catch (Throwable $error) {
            $this->logger->error($error->getMessage(), [
                'class' => get_class($error),
                'trace' => $error->getTraceAsString(),
            ]);

            yield new ResponseMessage($request->id, null, new ResponseError(
                $this->resolveErrorCode($error),
                $error->getMessage(),
                $error->getTraceAsString()
            ));
        }
    }

    private function resolveErrorCode(Throwable $error): int
    {
        if ($error instanceof HandlerNotFound) {
            return ErrorCodes::MethodNotFound;
        }

        return ErrorCodes::InternalError;
    }
}
