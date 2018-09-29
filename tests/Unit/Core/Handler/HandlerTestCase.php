<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

abstract class HandlerTestCase extends TestCase
{
    const EXAMPLE_PROCESS_ID = 1;

    abstract public function handler(): Handler;

    public function dispatch(string $method, array $params): array
    {
        $dispatcher = new MethodDispatcher(
            new DTLArgumentResolver(),
            new Handlers([
                $this->handler()
            ])
        );
        $request = new RequestMessage(self::EXAMPLE_PROCESS_ID, $method, $params);

        $messages = [];

        foreach ($dispatcher->dispatch($request) as $message) {
            $messages[] = $message;
        }

        return $messages;
    }
}
