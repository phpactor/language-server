<?php

namespace Phpactor\LanguageServer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Server\Server;
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

        $this->assertInstanceOf(Server::class, $server);
    }
}
