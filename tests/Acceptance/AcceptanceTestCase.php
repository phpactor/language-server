<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Loop;
use Amp\Loop\DriverFactory;
use Amp\Socket\ClientSocket;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Rpc\Request;
use Phpactor\LanguageServer\Core\Session\Workspace;
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

    protected function setUp()
    {
        Loop::set((new DriverFactory())->create());
    }

    protected function client(): TestClient
    {
        $server = LanguageServerBuilder::create()
            ->addSystemHandler(new TextDocumentHandler(new Workspace()))
            ->tcpServer()
            ->eventLoop(false)
            ->build();

        $server->start();

        $socket = \Amp\Socket\connect($server->address());
        $socket = \Amp\Promise\wait($socket);
        assert($socket instanceof ClientSocket);

        return new TestClient($socket);
    }

    protected function assertAllSuccess(array $responses)
    {
        foreach ($responses as $response) {
            $this->assertSuccess($response);
        }
    }

    protected function assertSuccess(Request $response)
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
