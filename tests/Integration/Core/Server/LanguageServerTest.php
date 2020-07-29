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
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Initializer\PredefinedInitializer;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\Transmitter\LspMessageFormatter;
use Psr\Log\NullLogger;
use function Amp\Iterator\fromIterable;
use function Amp\call;

class LanguageServerTest extends AsyncTestCase
{
    public function testDispatchesRequest(): Generator
    {
        $response = yield $this->dispatchRequest(
            new RequestMessage(1, 'foobar', []),
            function (RequestMessage $message) {
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
     * @return Promise<string>
     */
    private function dispatchRequest(RequestMessage $request, Closure $handler): Promise
    {
        return call(function () use ($handler, $request) {
            $this->setTimeout(100);

            $formatter = new LspMessageFormatter();
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
