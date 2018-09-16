<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use Exception;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Phpactor\LanguageServer\Core\Transport\ResponseError;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;

class ErrorCatchingDispatcherTest extends TestCase
{
    private $innerDispatcher;

    /**
     * @var ErrorCatchingDispatcher
     */
    private $dispatcher;


    public function setUp()
    {
        $this->innerDispatcher = $this->prophesize(Dispatcher::class);
        $this->dispatcher = new ErrorCatchingDispatcher($this->innerDispatcher->reveal());
    }

    public function testDispatches()
    {
        $request = new RequestMessage(1, 'foo', []);
        $expectedResponse = new ResponseMessage(1, []);
        $this->innerDispatcher->dispatch($request)->willReturn($expectedResponse);
        $response = $this->dispatcher->dispatch($request);

        $this->assertSame($expectedResponse, $response);
    }

    public function testConvertsExceptionsToErrorResponses()
    {
        $request = new RequestMessage(1, 'foo', []);
        $this->innerDispatcher->dispatch($request)->willThrow(new Exception('hello'));
        $response = $this->dispatcher->dispatch($request);

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertInstanceOf(ResponseError::class, $response->responseError);
        $this->assertEquals('hello', $response->responseError->message);
        $this->assertNotNull($response->responseError->data, 'contains stack trace info');
    }
}
