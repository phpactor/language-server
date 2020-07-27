<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Middleware;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use function Amp\Promise\wait;

class RequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testExceptionWhenNoMiddlewares(): void
    {
        $this->expectException(RuntimeException::class);
        (new RequestHandler([]))->handle(new RequestMessage(1, 'foo', []));
    }

    public function testMiddlewareReturnsResponse(): void
    {
        $request = new RequestMessage(1, 'foo', []);

        $response = new ResponseMessage(1, []);

        $middleware = $this->prophesize(Middleware::class);
        $middleware->process($request, Argument::type(RequestHandler::class))->willReturn(new Success($response));

        $handledResponse = (new RequestHandler([
            $middleware->reveal()
        ]))->handle($request);

        self::assertSame($response, wait($handledResponse));
    }

    public function testMiddlewareDelegatesToNextMiddleware(): void
    {
        $request = new RequestMessage(1, 'foo', []);

        $middleware1 = $this->prophesize(Middleware::class);
        $middleware1->process($request, Argument::type(RequestHandler::class))->will(function ($args) {
            return $args[1]->handle($args[0]);
        });

        $response = new ResponseMessage(1, []);
        $middleware2 = $this->prophesize(Middleware::class);
        $middleware2->process($request, Argument::type(RequestHandler::class))->willReturn(new Success($response));

        $handledResponse = (new RequestHandler([
            $middleware1->reveal(),
            $middleware2->reveal()
        ]))->handle($request);

        self::assertSame($response, wait($handledResponse));
    }
}
