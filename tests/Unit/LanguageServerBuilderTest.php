<?php

namespace Phpactor\LanguageServer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\LanguageServerBuilder;

class LanguageServerBuilderTest extends TestCase
{
    public function testBuild()
    {
        $server = LanguageServerBuilder::create()
            ->addSystemHandler(new class implements Handler {
                public function methods(): array
                {
                    return [];
                }
            })
            ->catchExceptions(true)
            ->tcpServer('127.0.0.1:8888')
            ->build();

        $this->assertInstanceOf(LanguageServer::class, $server);
    }
}
