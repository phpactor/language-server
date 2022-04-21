<?php

namespace Phpactor\LanguageServer\Tests\Unit\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Middleware\Exception\MiddlewareTerminated;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Event\WillShutdown;
use Phpactor\LanguageServer\Middleware\ShutdownMiddleware;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class ShutdownMiddlwareTest extends AsyncTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EventDispatcherInterface>
     */
    private ObjectProphecy $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

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

    public function testShutdownEmitsEvent(): Generator
    {
        $response = yield $this->createMiddleware()->process(
            new RequestMessage(1, ShutdownMiddleware::METHOD_SHUTDOWN, []),
            new RequestHandler()
        );
        $this->eventDispatcher->dispatch(new WillShutdown())->shouldHaveBeenCalled();
        self::assertEquals(new ResponseMessage(1, null), $response);
    }

    public function testShutdownTwiceReturnsError(): Generator
    {
        $middleware = $this->createMiddleware();
        yield $middleware->process(
            new RequestMessage(1, ShutdownMiddleware::METHOD_SHUTDOWN, []),
            new RequestHandler()
        );
        $response = yield $middleware->process(
            new RequestMessage(1, ShutdownMiddleware::METHOD_SHUTDOWN, []),
            new RequestHandler()
        );
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(ResponseError::class, $response->error);
        self::assertStringContainsString('Server is currently shutting down', $response->error->message);
    }

    private function createMiddleware(): ShutdownMiddleware
    {
        return new ShutdownMiddleware($this->eventDispatcher->reveal());
    }
}
