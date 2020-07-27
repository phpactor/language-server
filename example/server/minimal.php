#!/usr/bin/env php
<?php

use Amp\Success;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Middleware\ClosureMiddleware;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\LanguageServerBuilder;

require __DIR__ . '/../../vendor/autoload.php';

$builder = LanguageServerBuilder::create(new ClosureDispatcherFactory(
    function (MessageTransmitter $transmitter, InitializeParams $params) {
        return new MiddlewareDispatcher(
            new ClosureMiddleware(function (Message $message, RequestHandler $handler) {
                if (!$message instanceof RequestMessage) {
                    return $handler->handle($message);
                }

                return new Success(new ResponseMessage($message->id, 'Hello World!'));
            })
        );
    }
));

$builder
    ->build()
    ->run();
