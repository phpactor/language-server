<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\ClosureHandler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;

class HandlerMethodDispatcherTest extends AsyncTestCase
{
    public function testThrowsExceptionIfHandlerNotReturnPromise()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must return instance of Amp\Promise');

        $dispatcher = $this->createDispatcher([
            new ClosureHandler('foobar', function () {
                return 'foobar';
            })
        ]);
        yield $dispatcher->dispatch(
            new RequestMessage(1, 'foobar', [])
        );
    }

    public function testReturnsNullIfMessageIsANotification()
    {
        $dispatcher = $this->createDispatcher([
            new ClosureHandler('foobar', function () {
            })
        ]);

        $response = yield $dispatcher->dispatch(
            new NotificationMessage('foobar', [])
        );

        self::assertNull($response);
    }

    public function testReturnsValueFromHandler()
    {
        $dispatcher = $this->createDispatcher([
            new ClosureHandler('foobar', function (array $params) {
                return new Success('foobar: '.$params['bar']);
            })
        ]);

        $response = yield $dispatcher->dispatch(
            new RequestMessage(2, 'foobar', ['bar' => 'foo'])
        );

        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertEquals('foobar: foo', $response->result);
        self::assertEquals(2, $response->id);
    }

    private function createDispatcher(array $handlers): HandlerMethodDispatcher
    {
        return new HandlerMethodDispatcher(
            new Handlers($handlers),
            new HandlerMethodResolver()
        );
    }
}
