<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Loop;
use Amp\Loop\DriverFactory;
use Amp\Socket\ResourceSocket;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class AcceptanceTestCase extends TestCase
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var InputStream
     */
    private $input;

    /**
     * @var resource
     */
    private $stream;

    protected function setUp(): void
    {
        Loop::set((new DriverFactory())->create());
    }

    protected function client(): TestClient
    {
        $server = LanguageServerBuilder::create()
            ->addSystemHandler(new TextDocumentHandler(new NullEventDispatcher()))
            ->tcpServer()
            ->eventLoop(false)
            ->build();

        $server->start();

        $socket = \Amp\Socket\connect($server->address());
        $socket = \Amp\Promise\wait($socket);
        assert($socket instanceof ResourceSocket);

        return new TestClient($socket);
    }

    protected function assertAllSuccess(array $responses)
    {
        foreach ($responses as $response) {
            $this->assertSuccess($response);
        }
    }

    protected function assertSuccess(RawMessage $response)
    {
        if (!isset($response->body()['responseError'])) {
            return;
        }
        $this->fail(sprintf(
            '%s'.PHP_EOL.'%s',
            $response->body()['responseError']['message'],
            ''//$response->body()['responseError']['data']
        ));
    }
}
