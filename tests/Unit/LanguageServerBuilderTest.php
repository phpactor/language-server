<?php

namespace Phpactor\LanguageServer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventSubscriber;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\ServerTester;

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
}
