<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Phpactor\LanguageServer\Core\Handler\ClosureHandler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use RuntimeException;
use function Amp\call;
use function Amp\delay;

class HandlerMethodRunnerTest extends AsyncTestCase
{
    public function testExceptionIfNotRequestOrNotification()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Message must either be a ');

        $runner = $this->createRunner([
            new ClosureHandler('foobar', function () {
                return 'foobar';
            })
        ]);
        yield $runner->dispatch(
            new ResponseMessage(1, 'foobar')
        );
    }

    public function testThrowsExceptionIfHandlerNotReturnPromise()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must return instance of Amp\Promise');

        $dispatcher = $this->createRunner([
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
        $dispatcher = $this->createRunner([
            new ClosureHandler('foobar', function (): void {
            })
        ]);

        $response = yield $dispatcher->dispatch(
            new NotificationMessage('foobar', [])
        );

        self::assertNull($response);
    }

    public function testReturnsValueFromHandler()
    {
        $dispatcher = $this->createRunner([
            new ClosureHandler('foobar', function (string $bar) {
                return new Success('foobar: '.$bar);
            })
        ]);

        $response = yield $dispatcher->dispatch(
            new RequestMessage(2, 'foobar', ['bar' => 'foo'])
        );

        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertEquals('foobar: foo', $response->result);
        self::assertEquals(2, $response->id);
    }

    public function testToleratesTryingToCancelNonRunningRequest(): void
    {
        $dispatcher = $this->createRunner([
            new ClosureHandler('foobar', function () {
                return call(function () {
                    yield delay(10);
                });
            })
        ]);

        $response = $dispatcher->dispatch(
            new RequestMessage(1, 'foobar', ['bar' => 'foo'])
        );

        delay(5);

        $dispatcher->cancelRequest(2);
        $this->addToAssertionCount(1);
    }

    public function testCancelsRequest()
    {
        $this->expectException(CancelledException::class);

        $dispatcher = $this->createRunner([
            new ClosureHandler('foobar', function (string $bar, CancellationToken $token) {
                return call(function () use ($token) {
                    yield delay(100);
                    $token->throwIfRequested();
                });
            })
        ]);

        $promise = $dispatcher->dispatch(
            new RequestMessage(1, 'foobar', ['bar' => 'foo'])
        );

        $dispatcher->cancelRequest(1);

        yield $promise;
    }

    private function createRunner(array $handlers): HandlerMethodRunner
    {
        return new HandlerMethodRunner(
            new Handlers(...$handlers)
        );
    }
}
