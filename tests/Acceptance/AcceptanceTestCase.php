<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Connection\SimpleConnection;
use Phpactor\LanguageServer\Core\IO\BufferIO;
use Phpactor\LanguageServer\Core\Protocol\LanguageServerProtocol\Reader;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;
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

    /**
     * @var BufferIO
     */
    private $io;

    protected function setUp()
    {
        $this->io = new BufferIO();

        $logger = new class extends AbstractLogger {
            public function log($level, $message, array $context = []) {
                //fwrite(STDERR, 'TESTSERV:'.$message.PHP_EOL);
            }
        };

        $this->server = LanguageServerBuilder::create($logger)
            ->coreHandlers()
            ->withConnection(new SimpleConnection($this->io))
            ->build();
    }

    protected function playback(string $scriptName)
    {
        $path = $this->scriptPath($scriptName);

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Playback script "%s" does not exit',
                $path
            ));
        }
        $this->io->add(file_get_contents($path));
        $this->io->add(file_get_contents($this->scriptPath('exit.script')));

        $this->server->start();

        $reader = new Reader(new NullLogger());

        $io = new BufferIO();
        $io->add($this->io->out());
        while (true) {
            yield $reader->readRequest($io);
        }
    }

    public function input(): InputStream
    {
        return $this->input;
    }

    private function scriptPath(string $scriptName)
    {
        $path = __DIR__ . '/autozimzu/' . $scriptName;
        return $path;
    }
}
