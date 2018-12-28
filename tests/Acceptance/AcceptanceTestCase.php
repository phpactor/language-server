<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Amp\Loop;
use Amp\Loop\DriverFactory;
use Amp\Socket\ClientSocket;
use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Connection\SimpleConnection;
use Phpactor\LanguageServer\Core\IO\BufferIO;
use Phpactor\LanguageServer\Core\Protocol\LspReader;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;
use Phpactor\LanguageServer\Core\Server\Parser\LanguageServerProtocolParser;
use Phpactor\LanguageServer\Core\Server\TcpServer;
use Phpactor\LanguageServer\Core\Transport\Request;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Clue\React\Block;
use React\Promise\Stream;
use RuntimeException;
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
