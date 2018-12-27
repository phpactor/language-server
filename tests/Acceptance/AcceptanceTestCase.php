<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Connection\SimpleConnection;
use Phpactor\LanguageServer\Core\IO\BufferIO;
use Phpactor\LanguageServer\Core\Protocol\LspReader;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;
use Phpactor\LanguageServer\Core\TcpServer;
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

    protected function playback(string $scriptPath)
    {
        $loop = Factory::create();

        $server = new TcpServer($loop, new NullLogger(), 0);

        $connector = new Connector($loop);

        $input = file_get_contents(__DIR__ . '/scripts/'.$scriptPath);

        $result = $connector->connect($server->address())
            ->then(function (ConnectionInterface $connection) use ($input) {
                $connection->write($input);

                return Stream\buffer($connection);
            })
        ;

        $response = Block\await($result, $loop, 1.0);
        $process->stop();

        return $response;
    }
}
