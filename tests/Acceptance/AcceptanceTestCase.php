<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Loop;
use Amp\Loop\DriverFactory;
use Amp\Socket\ClientSocket;
use PHPUnit\Framework\TestCase;
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
        $address = '127.0.0.1:1337';
        $server = LanguageServerBuilder::create()->build($address);

        $server->start();

        $socket = \Amp\Socket\connect($address);
        $socket = \Amp\Promise\wait($socket);
        assert($socket instanceof ClientSocket);

        return new TestClient($socket);
    }
}
