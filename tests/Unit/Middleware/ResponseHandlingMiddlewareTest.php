<?php

namespace Phpactor\LanguageServer\Tests\Unit\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Generator;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Middleware\ClosureMiddleware;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Middleware\ResponseHandlingMiddleware;

class ResponseHandlingMiddlewareTest extends AsyncTestCase
{
    /**
     * @var ResponseWatcher
     */
    private $watcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->watcher = new DeferredResponseWatcher();
    }

    public function testDelegatesToNextHandlerIfMessageIsNotResponse(): Generator
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
    }

    public function testResolvesResponsePromise(): Generator
    {
        $promise = $this->watcher->waitForResponse(1);
        $response = new ResponseMessage(1, 'foobar');

        $responseResponse = yield $this->createMiddleware()->process(
            $response,
            new RequestHandler([
                new ClosureMiddleware(function () {
                    return new Success(new ResponseMessage(1, null));
                })
            ])
        );

        $resolvedResponse = yield $promise;

        self::assertNull($responseResponse);
        self::assertSame($response, $resolvedResponse);
    }

    public function testResolves(): Generator
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
    }

    private function createMiddleware(): ResponseHandlingMiddleware
    {
        return new ResponseHandlingMiddleware($this->watcher);
    }
}
