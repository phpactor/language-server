<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Amp\Success;
use Exception;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Psr\Log\LoggerInterface;

class ErrorCatchingDispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerDispatcher;

    /**
     * @var ObjectProphecy
     */
    private $logger;

    /**
     * @var ErrorCatchingDispatcher
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->innerDispatcher = $this->prophesize(Dispatcher::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->dispatcher = new ErrorCatchingDispatcher(
            $this->innerDispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testCatchesErrorsThrownDuringInnerDispatch()
    {
        $message = new RequestMessage(1, 'hello', []);
        $handlers = new Handlers([]);
        $this->innerDispatcher->dispatch($handlers, $message, [])->willThrow(new Exception('Hello'));

        $response = \Amp\Promise\wait($this->dispatcher->dispatch($handlers, $message, []));

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertInstanceOf(ResponseError::class, $response->responseError);
        $this->assertEquals('Hello', $response->responseError->message);
    }

    public function testCatchesHandlerNotFound()
    {
        $message = new RequestMessage(1, 'hello', []);
        $handlers = new Handlers([]);
        $this->innerDispatcher->dispatch($handlers, $message, [])->willThrow(new HandlerNotFound('Hello'));

        $response = \Amp\Promise\wait($this->dispatcher->dispatch($handlers, $message, []));

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertInstanceOf(ResponseError::class, $response->responseError);
        $this->assertEquals(ErrorCodes::MethodNotFound, $response->responseError->code);
        $this->assertEquals('Hello', $response->responseError->message);
    }

    public function testReturnsResultsFromInnerDispatcher()
    {
        $message = new RequestMessage(1, 'hello', []);
        $handlers = new Handlers([]);

        $this->innerDispatcher->dispatch($handlers, $message, [])->will(function () {
            return new Success(new NotificationMessage('hello', []));
        });

        $response = \Amp\Promise\wait($this->dispatcher->dispatch($handlers, $message, []));

        $this->assertInstanceOf(NotificationMessage::class, $response);
    }
}
