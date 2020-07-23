<?php

namespace Phpactor\LanguageServer\Tests\Integration\Core\Server;

use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\IteratorStream;
use Amp\ByteStream\OutputBuffer;
use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Amp\ByteStream\Test\BufferTest;
use Amp\Iterator;
use Amp\Loop;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Closure;
use Generator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Core\Server\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Server\Initializer\PredefinedInitializer;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\Parser\LspMessageReader;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageFormatter;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use function Amp\Iterator\fromIterable;
use function Amp\call;
use function Amp\delay;
use function Safe\fflush;
use function Safe\fopen;
use function Safe\fread;
use function Safe\rewind;

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

            $formatter = new MessageFormatter();
            $message = $formatter->write($request);

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
