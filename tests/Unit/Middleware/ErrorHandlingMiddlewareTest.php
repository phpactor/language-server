<?php

namespace Phpactor\LanguageServer\Tests\Unit\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Generator;
use Phpactor\LanguageServer\Core\Handler\HandlerNotFound;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Middleware\ClosureMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Psr\Log\Test\TestLogger;
use RuntimeException;

class ErrorHandlingMiddlewareTest extends AsyncTestCase
{
    /**
     * @var TestLogger
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new TestLogger();
    }

    public function testDelegatesToNextHandlerIfMessageIsNotRequest(): Generator
    {
        $response = yield $this->createMiddleware()->process(
            new NotificationMessage('foobar'),
            new RequestHandler([
                new ClosureMiddleware(function () {
                    return new Success(new ResponseMessage(1, null));
                })
            ])
        );


        self::assertInstanceOf(ResponseMessage::class, $response);
        self::assertEquals([], $this->logger->records);
    }

    public function testRethrowsServerControlException(): Generator
    {
        $this->expectException(ExitSession::class);

        yield $this->createMiddleware()->process(
            new NotificationMessage('foobar'),
            new RequestHandler([
                new ClosureMiddleware(function (): void {
                    throw new ExitSession('please');
                })
            ])
        );
    }

    public function testLogsAndReturnsNullForNoticationException(): Generator
    {
        $response = yield $this->createMiddleware()->process(
            new NotificationMessage('foobar'),
            new RequestHandler([
                new ClosureMiddleware(function (): void {
                    throw new RuntimeException('please');
                })
            ])
        );

        self::assertNull($response);
        self::assertTrue($this->logger->hasErrorThatContains('please'));
    }

    public function testDoesNotLogButReturnsErrorResponseForRequest(): Generator
    {
        $response = yield $this->createMiddleware()->process(
            new RequestMessage(1, 'foobar', []),
            new RequestHandler([
                new ClosureMiddleware(function (): void {
                    throw new RuntimeException('please');
                })
            ])
        );

        self::assertInstanceOf(ResponseMessage::class, $response);
        assert($response instanceof ResponseMessage);
        self::assertInstanceOf(ResponseError::class, $response->error);
        self::assertEquals('Exception [RuntimeException] please', $response->error->message);
        self::assertEquals(ErrorCodes::InternalError, $response->error->code);
    }

    public function testMethodNotFoundCodeForHandlerNotFound(): Generator
    {
        $response = yield $this->createMiddleware()->process(
            new RequestMessage(1, 'foobar', []),
            new RequestHandler([
                new ClosureMiddleware(function (): void {
                    throw new HandlerNotFound('please');
                })
            ])
        );

        self::assertInstanceOf(ResponseMessage::class, $response);
        assert($response instanceof ResponseMessage);
        self::assertInstanceOf(ResponseError::class, $response->error);
        self::assertEquals('Exception [Phpactor\LanguageServer\Core\Handler\HandlerNotFound] please', $response->error->message);
        self::assertEquals(ErrorCodes::MethodNotFound, $response->error->code);
    }

    private function createMiddleware(): ErrorHandlingMiddleware
    {
        return new ErrorHandlingMiddleware($this->logger);
    }
}
