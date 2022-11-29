<?php

namespace Phpactor\LanguageServer\Middleware;

use Amp\CancelledException;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Exception\ServerControl;
use Psr\Log\LoggerInterface;
use Throwable;
use function Amp\call;

class ErrorHandlingMiddleware implements Middleware
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            try {
                return yield $handler->handle($request);
            } catch (ServerControl $exception) {
                throw $exception;
            } catch (CancelledException $cancelled) {
                if (!$request instanceof RequestMessage) {
                    return new Success(null);
                }

                return new Success(new ResponseMessage(
                    $request->id,
                    null,
                    new ResponseError(
                        ErrorCodes::RequestCancelled,
                        sprintf('Request %d (%s) cancelled', $request->id, $request->method),
                    )
                ));
            } catch (Throwable $error) {
                $message = sprintf('Exception [%s] %s', get_class($error), $error->getMessage());
                $this->logger->error(sprintf(
                    'Error when handling "%s" (%s): %s',
                    get_class($request),
                    json_encode($request),
                    $message
                ));

                if (!$request instanceof RequestMessage) {
                    return new Success(null);
                }

                return new Success(new ResponseMessage(
                    $request->id,
                    null,
                    new ResponseError(
                        $this->resolveErrorCode($error),
                        sprintf('%s at %s#%s', $message, $error->getFile(), $error->getLine()),
                        $error->getTraceAsString()
                    )
                ));
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
