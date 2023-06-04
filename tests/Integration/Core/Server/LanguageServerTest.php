<?php

namespace Phpactor\LanguageServer\Tests\Integration\Core\Server;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\IteratorStream;
use Amp\ByteStream\OutputBuffer;
use Amp\Loop;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Closure;
use Generator;
use PHPUnit\Framework\Assert;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Initializer\PredefinedInitializer;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageFormatter;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageSerializer;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageSerializer;
use Psr\Log\NullLogger;
use function Amp\Iterator\fromIterable;
use function Amp\call;
use Exception;

class LanguageServerTest extends AsyncTestCase
{
    /**
     * @return Generator<Promise<string>>
     */
    public function testDispatchesRequest(): Generator
    {
        $response = yield $this->dispatchRequest(
            new RequestMessage(1, 'foobar', []),
            function (Message $message) {
                if (!$message instanceof RequestMessage) {
                    throw new Exception('not a request');
                }
                Assert::assertEquals('foobar', $message->method);
                return new Success(new ResponseMessage(1, [
                    'foo' => 'bar',
                ]));
            }
        );

        Assert::assertInstanceOf(RawMessage::class, $response);
        Assert::assertEquals(['foo' => 'bar'], $response->body()['result']);
    }
    /**
     * @return Generator<Promise<string>>
     */
    public function testHandlesMalformedRequest(): Generator
    {
        $serializer = new TestMessageSerializer('{"id":3,"foo":"bar"}');
        $response = yield $this->dispatchRequest(
            new RequestMessage(1, 'foobar', []),
            function (Message $message) {
                return new Success(new ResponseMessage(1, [
                    'foo' => 'bar',
                ]));
            },
            $serializer
        );

        Assert::assertInstanceOf(RawMessage::class, $response);
        self::assertEquals(255, $response->body()['error']['code']);

    }
    /**
     * @return Promise<string>
     * @param Closure(Message): Promise<ResponseMessage|null> $handler
     */
    private function dispatchRequest(RequestMessage $request, Closure $handler, ?MessageSerializer $serializer = null): Promise
    {
        $serializer = $serializer ?: new LspMessageSerializer();
        return call(function () use ($handler, $request, $serializer) {
            $this->setTimeout(100);

            $formatter = new LspMessageFormatter($serializer);
            $message = $formatter->format($request);

            $output = new OutputBuffer();
            $stream = new ResourceDuplexStream(
                new IteratorStream(fromIterable([$message])),
                $output
            );

            $server = new LanguageServer(
                new ClosureDispatcherFactory(function () use ($handler) {
                    return new ClosureDispatcher($handler);
                }),
                new NullLogger(),
                new ResourceStreamProvider($stream, new NullLogger()),
                new PredefinedInitializer()
            );


            Loop::delay(10, function () use ($server) {
                yield $server->shutdown();
            });

            yield $server->start();

            $parser = new LspMessageReader(new InMemoryStream(yield $output));

            return yield $parser->wait();
        });
    }
}
