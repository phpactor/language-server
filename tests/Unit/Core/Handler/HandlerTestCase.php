<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\ArgumentResolver\IdentityArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

abstract class HandlerTestCase extends TestCase
{
    const EXAMPLE_PROCESS_ID = 1;

    abstract public function handler(): Handler;

    public function dispatch(string $method, array $params): ResponseMessage
    {
        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver(),
            new Handlers([
                $this->handler()
            ])
        );
        $request = new RequestMessage(self::EXAMPLE_PROCESS_ID, $method, $params);
        return $dispatcher->dispatch($request);
    }
}
