<?php

namespace Phpactor\LanguageServer\Tests\Integration\Middleware\Handlers;

use Amp\Loop;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Test\ChannelStream;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Server\Initializer\PredefinedInitializer;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\StreamProvider\ResourceStreamProvider;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Psr\Log\LoggerInterface;

abstract class HandlersTestCase extends AsyncTestCase
{
    /**
     * @param Handlers $handlers The handlers to be registered with the server
     * @param int $lifetime The server will be shut down after this amount of time in milliseconds
     * @return Promise<array{LanguageServer, ChannelStream}>
     */
    protected function startServer(Handlers $handlers, int $lifetime = 10): Promise
    {
        return \Amp\call(function () use ($handlers, $lifetime) {
            $requests = new ChannelStream();
            $responses = new ChannelStream();

            $serverStream = new ResourceDuplexStream($requests, $responses);
            $clientStream = new ResourceDuplexStream($responses, $requests);

            $logger = $this->createMock(LoggerInterface::class);
            $server = new LanguageServer(
                $this->createDispatcherFactory($handlers),
                $logger,
                new ResourceStreamProvider($serverStream, $logger),
                new PredefinedInitializer()
            );

            Loop::delay($lifetime, function () use ($server) {
                yield $server->shutdown();
            });

            yield $server->start();

            return [$server, $clientStream];
        });
    }

    private function createDispatcherFactory(Handlers $handlers): DispatcherFactory
    {
        $logger = $this->createMock(LoggerInterface::class);

        $runner = new HandlerMethodRunner(
            $handlers,
            new ChainArgumentResolver(
                new LanguageSeverProtocolParamsResolver(),
                new PassThroughArgumentResolver(),
            ),
        );

        return new ClosureDispatcherFactory(
            fn () => new MiddlewareDispatcher(
                new ErrorHandlingMiddleware($logger),
                new CancellationMiddleware($runner),
                new HandlerMiddleware($runner),
            ),
        );
    }
}
