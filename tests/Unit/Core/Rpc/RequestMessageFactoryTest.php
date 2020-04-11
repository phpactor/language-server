<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Rpc;

use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use RuntimeException;

class RequestMessageFactoryTest extends TestCase
{
    public function testExceptionOnInvalidKeys()
    {
        $this->expectException(RuntimeException::class);
        $request = new RawMessage([], ['foo' => 'bar']);
        RequestMessageFactory::fromRequest($request);
    }

    public function testExceptionMissingKeys()
    {
        $this->expectException(RuntimeException::class);
        $request = new RawMessage([], []);
        RequestMessageFactory::fromRequest($request);
    }

    public function testReturnsRequestMessage()
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

    public function testReturnsRequestMessageForNotification()
    {
        $request = new RawMessage([], [
            'jsonrpc' => 2.0,
            'id' => null,
            'method' => 'foobar',
            'params' => ['one' => 'two']
        ]);
        $request = RequestMessageFactory::fromRequest($request);
        self::assertInstanceOf(NotificationMessage::class, $request);
        $this->assertEquals('foobar', $request->method);
        $this->assertEquals(2.0, $request->jsonrpc);
        $this->assertEquals(['one' => 'two'], $request->params);
    }
}
