<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Rpc\RequestMessageFactory;
use RuntimeException;

class RequestMessageFactoryTest extends TestCase
{
    public function testExceptionOnInvalidKeys()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request has invalid keys: "foo", valid keys: "jsonrpc", "id", "method", "params"');
        $request = new Request([], ['foo' => 'bar']);
        RequestMessageFactory::fromRequest($request);
    }

    public function testExceptionMissingKeys()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing required keys');
        $request = new Request([], []);
        RequestMessageFactory::fromRequest($request);
    }

    public function testReturnsRequestMessage()
    {
        $request = new Request([], [
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
}
