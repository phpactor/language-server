<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Success;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\CancellingMethodDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\TestUtils\PHPUnit\TestCase;
use function Amp\Promise\wait;
use function Amp\Promise\all;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class CancellingMethodDispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerDispatcher;

    /**
     * @var CancellingMethodDispatcher
     */
    private $dispatcher;

    /**
     * @var Handlers
     */
    private $handlers;

    /**
     * @var NullLogger
     */
    private $logger;

    protected function setUp(): void
    {
        $this->innerDispatcher = $this->prophesize(Dispatcher::class);
        $this->logger = new NullLogger();
        $this->dispatcher = new CancellingMethodDispatcher($this->innerDispatcher->reveal(), $this->logger);
        $this->handlers = new Handlers();
    }

    public function testCancelsMethod()
    {
        $request1 = $this->createRequest(1, 'longRequest', []);
        $request2 = $this->createRequest(null, '$/cancelRequest', [
            'id' => 1,
        ]);

        $this->innerDispatcher->dispatch(
            $this->handlers,
            $request1,
            Argument::any()
        )->will(function ($args) {
            return \Amp\call(function (Handlers $handlers, RequestMessage $message, array $extra) {
                $token = $extra['_cancel'];
                assert($token instanceof CancellationToken);

                while (true) {
                    try {
                        $token->throwIfRequested();
                    } catch (CancelledException $token) {
                        break;
                    }

                    yield new Delayed(10);
                }

                return 'I terminated upon cancellation';
            }, ...$args);
        });

        $response1 = $this->dispatcher->dispatch($this->handlers, $request1, []);
        $response2 = $this->dispatcher->dispatch($this->handlers, $request2, []);

        self::assertEquals(
            [
                'I terminated upon cancellation',
                null
            ],
            wait(all([$response1, $response2]))
        );
    }

    public function testDelegatesToInnerMethod()
    {
        $request = $this->createRequest(1, 'foobar', []);
        $expectedResponse = $this->createResponse(1);

        $this->innerDispatcher->dispatch(
            $this->handlers,
            $request,
            Argument::that(function (array $args) {
                self::assertInstanceOf(CancellationToken::class, $args['_cancel']);
                return true;
            })
        )->willReturn(new Success($expectedResponse))->shouldBeCalled();

        $response = $this->dispatcher->dispatch(
            $this->handlers,
            $request,
            [
            ]
        );

        self::assertEquals($expectedResponse, wait($response));
    }

    public function testCancelsUnknownRequest()
    {
        $request = $this->createRequest(null, '$/cancelRequest', [
            'id' => 4,
        ]);

        $response = $this->dispatcher->dispatch($this->handlers, $request, []);

        self::assertEquals(
            null,
            wait($response)
        );
    }

    private function createRequest(?int $id, string $method, array $params): Message
    {
        if (null === $id) {
            return new NotificationMessage($method, $params);
        }
        return new RequestMessage($id, $method, $params);
    }

    private function createResponse(int $id, ?int $errorCode = null, string $errorMessage = ''): ResponseMessage
    {
        if ($errorCode) {
            return new ResponseMessage($id, null, new ResponseError($errorCode, $errorMessage));
        }

        return new ResponseMessage($id, null);
    }
}
