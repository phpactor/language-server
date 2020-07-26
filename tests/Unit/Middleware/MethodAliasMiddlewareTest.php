<?php

namespace Phpactor\LanguageServer\Tests\Unit\Middleware;

use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Middleware\ClosureMiddleware;
use Phpactor\LanguageServer\Middleware\MethodAliasMiddleware;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Middleware\Exception\MiddlewareTerminated;
use Generator;
use Amp\PHPUnit\AsyncTestCase;

class MethodAliasMiddlewareTest extends AsyncTestCase
{
    public function testDelegatesToNextHandlerIfMessageIsNotRequest(): Generator
    {
        $this->expectException(MiddlewareTerminated::class);
        self::assertNull(
            yield $this->createMiddleware()->process(
                new NotificationMessage('foobar'),
                new RequestHandler()
            )
        );
    }

    public function testChangesMethodName(): Generator
    {
        yield $this->createMiddleware([
            'foobar' => 'barfoo',
        ])->process(
            new NotificationMessage('foobar'),
            new RequestHandler([
                new ClosureMiddleware(function (Message $message, RequestHandler $handler) {
                    self::assertEquals('barfoo', $message->method);
                    return new Success(null);
                })
            ])
        );
    }

    public function testIgnoresUnmappedMethod(): Generator
    {
        yield $this->createMiddleware([
            'boobar' => 'barfoo',
        ])->process(
            new NotificationMessage('foobar'),
            new RequestHandler([
                new ClosureMiddleware(function (Message $message, RequestHandler $handler) {
                    self::assertEquals('foobar', $message->method);
                    return new Success(null);
                })
            ])
        );
    }

    private function createMiddleware($aliasMap = []): Middleware
    {
        return new MethodAliasMiddleware($aliasMap);
    }
}
