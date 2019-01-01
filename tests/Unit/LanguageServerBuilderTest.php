<?php

namespace Phpactor\LanguageServer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventSubscriber;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\LanguageServerBuilder;

class LanguageServerBuilderTest extends TestCase
{
    public function testBuild()
    {
        $server = LanguageServerBuilder::create()
            ->addHandler(new class implements Handler {
                public function methods(): array
                {
                    return [];
                }
            })
            ->catchExceptions(true)
            ->useDefaultHandlers(true)
            ->tcpServer('127.0.0.1:8888')
            ->build();

        $this->assertInstanceOf(LanguageServer::class, $server);
    }

    public function testHandlersSubscribeToEvents()
    {
        $server = LanguageServerBuilder::create()
            ->addHandler(new class implements Handler, EventSubscriber {
                public function methods(): array
                {
                    return [];
                }

                public function events(): array
                {
                    return [
                        'my_event' => 'foo',
                    ];
                }

                public function foo(): void
                {
                }
            })
            ->build();

        $this->assertInstanceOf(LanguageServer::class, $server);
    }

    public function testHandlerFactories()
    {
        $handler = new class implements Handler {
            public function methods(): array
            {
                return [
                    'my/foo' => 'foo'
                ];
            }

            public function foo(): void
            {
            }
        };

        $dispatcher = LanguageServerBuilder::create()
            ->addHandlerFactory('my/foo', function () use ($handler) {
                return $handler;
            })
            ->buildDispatcher();
        $responses = iterator_to_array($dispatcher->dispatch(new RequestMessage(1, 'initialize', [
            'rootUri' => __DIR__,
        ])));
        $responses = iterator_to_array($dispatcher->dispatch(new RequestMessage(1, 'my/foo', [])));
        $this->assertCount(0, $responses, 'No errors returned');
    }
}
