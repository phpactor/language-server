<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Rpc;

use DTL\Invoke\Exception\RequiredKeysMissing;
use DTL\Invoke\Exception\UnknownKeys;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;

class RequestMessageFactoryTest extends TestCase
{
    public function testExceptionOnInvalidKeys(): void
    {
        $this->expectException(UnknownKeys::class);
        $request = new RawMessage([], ['foo' => 'bar']);
        RequestMessageFactory::fromRequest($request);
    }

    public function testExceptionMissingKeys(): void
    {
        $this->expectException(RequiredKeysMissing::class);
        $request = new RawMessage([], []);
        RequestMessageFactory::fromRequest($request);
    }

    public function testReturnsRequestMessage(): void
    {
        $request = new RawMessage([], [
            'jsonrpc' => 2.0,
            'id' => 1,
            'method' => 'foobar',
            'params' => ['one' => 'two']
        ]);
        $request = RequestMessageFactory::fromRequest($request);
        $this->assertEquals('foobar', $request->method);
        $this->assertEquals(2.0, $request->jsonrpc);
        $this->assertEquals(1, $request->id);
        $this->assertEquals(['one' => 'two'], $request->params);
    }

    public function testReturnsRequestMessageForNotification(): void
    {
        $notification = new RawMessage([], [
            'jsonrpc' => 2.0,
            'id' => null,
            'method' => 'foobar',
            'params' => ['one' => 'two']
        ]);
        $notification = RequestMessageFactory::fromRequest($notification);
        self::assertInstanceOf(NotificationMessage::class, $notification);
        $this->assertEquals('foobar', $notification->method);
        $this->assertEquals(2.0, $notification->jsonrpc);
        $this->assertEquals(['one' => 'two'], $notification->params);
    }

    public function testReturnsRequestMessageForResponse(): void
    {
        $response = new RawMessage([], [
            'jsonrpc' => 2.0,
            'id' => 123,
            'result' => 'foobar',
            'error' => null
        ]);
        $response = RequestMessageFactory::fromRequest($response);
        self::assertInstanceOf(ResponseMessage::class, $response);
        $this->assertEquals('foobar', $response->result);
    }
}
