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

    public function testBuildWithRecorder()
    {
        $name = tempnam(sys_get_temp_dir(), 'language-server-test');
        $server = LanguageServerBuilder::create()
            ->addSystemHandler(new class implements Handler {
                public function methods(): array
                {
                    return [];
                }
            })
            ->recordTo($name)
            ->buildServerTester();

        $server->dispatch('foo', ['foo' => 'bar']);
        $this->assertContains('{"id":1,"method":"foo","params":{"foo":"bar"},"jsonrpc":"2.0"}', file_get_contents($name));
        unlink($name);
    }
}
