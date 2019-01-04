<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
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
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage(), [
                'class' => get_class($throwable),
                'trace' => $throwable->getTraceAsString(),
            ]);

            yield new ResponseMessage($request->id, null, new ResponseError(
                ErrorCodes::InternalError,
                $throwable->getMessage(),
                $throwable->getTraceAsString()
            ));
        }
    }
}
