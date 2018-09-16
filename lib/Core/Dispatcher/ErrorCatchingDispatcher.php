<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Exception;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Transport\ErrorCodes;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseError;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

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

    public function dispatch(RequestMessage $request): ResponseMessage
    {
        try {
            return $this->innerDispatcher->dispatch($request);
        } catch (Exception $exception) {
            return new ResponseMessage($request->id, null, new ResponseError(
                ErrorCodes::InternalError,
                $exception->getMessage(),
                $exception->getTraceAsString()
            ));
        }
    }
}
