<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use Exception;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Prophecy\Argument;
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

    public function setUp()
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
        $this->innerDispatcher->dispatch($message)->willThrow(new Exception('Hello'));

        $this->logger->error('Hello', Argument::cetera())->shouldBeCalled();

        $responses = $this->dispatcher->dispatch($message);

        $response = $responses->current();
        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertInstanceOf(ResponseError::class, $response->responseError);
        $this->assertEquals('Hello', $response->responseError->message);
    }

    public function testReturnsResultsFromInnerDispatcher()
    {
        $message = new RequestMessage(1, 'hello', []);

        $this->innerDispatcher->dispatch($message)->will(function () {
            yield new NotificationMessage('hello', []);
        });

        $responses = $this->dispatcher->dispatch($message);

        $response = $responses->current();
        $this->assertInstanceOf(NotificationMessage::class, $response);
    }
}
