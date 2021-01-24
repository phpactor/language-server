<?php

namespace Phpactor\LanguageServer\Tests\Unit\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Phpactor\LanguageServerProtocol\InitializeResult;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Middleware\Exception\MiddlewareTerminated;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class InitializeMiddlewareTest extends AsyncTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EventDispatcherInterface>
     */
    private $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
    }

    public function testReturnsOnInitializedNotification(): Generator
    {
        self::assertNull(
            yield $this->createMiddleware()->process(
                new NotificationMessage('initialized'),
                new RequestHandler()
            )
        );
        $this->dispatcher->dispatch(new Initialized())->shouldHaveBeenCalled();
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

    public function testExceptionIfInitializedTwice(): Generator
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Second initialize');

        $middleware = $this->createMiddleware();

        yield $middleware->process(
            new RequestMessage(1, 'initialize', [
                'capabilities' => [],
            ]),
            new RequestHandler()
        );
        yield $middleware->process(
            new RequestMessage(1, 'initialize', [
                'capabilities' => [],
            ]),
            new RequestHandler()
        );
    }

    public function testReturnsInitializedResponse(): Generator
    {
        $middleware = $this->createMiddleware([], [
            'server_info' => 'please',
        ]);

        $response = yield $middleware->process(
            new RequestMessage(1, 'initialize', [
                'capabilities' => [],
            ]),
            new RequestHandler()
        );
        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(InitializeResult::class, $response->result);
        self::assertEquals([
            'server_info' => 'please',
        ], $response->result->serverInfo);
    }

    public function testHandlersCanRegisterCapabiltiies(): Generator
    {
        $handler = $this->prophesize(Handler::class)
            ->willImplement(CanRegisterCapabilities::class);
        $handler->methods()->willReturn([
            'foo' => 'bar',
        ]);
        $handler->registerCapabiltiies(Argument::type(ServerCapabilities::class))->will(function (array $args): void {
            $capabilities = $args[0];
            assert($capabilities instanceof ServerCapabilities);
            $capabilities->hoverProvider = true;
        })->shouldBeCalled();

        $middleware = $this->createMiddleware([
            $handler->reveal()
        ]);

        $response = yield $middleware->process(
            new RequestMessage(1, 'initialize', [
                'capabilities' => [],
            ]),
            new RequestHandler()
        );

        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertInstanceOf(InitializeResult::class, $response->result);
        self::assertTrue($response->result->capabilities->hoverProvider);
    }

    private function createMiddleware(array $handlers = [], array $serverInfo = []): InitializeMiddleware
    {
        return new InitializeMiddleware(
            new Handlers(...$handlers),
            $this->dispatcher->reveal(),
            $serverInfo
        );
    }
}
