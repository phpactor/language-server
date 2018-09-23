<?php

namespace Phpactor\LanguageServer\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
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
        $this->startServer();
    }

    protected function sendRequest(string $request)
    {
        fwrite($this->stream, sprintf("Content-Length:%s\r\n\r\n%s", mb_strlen($request), $request));
        usleep(50000);
    }

    protected function readOutput()
    {
        return fread($this->stream, 1000);
    }

    private function startServer()
    {
        $address = '127.0.0.1:8888';
        $process = new Process([
            __DIR__ . '/../../bin/serve.php',
            '--type=tcp',
            '--address=' . $address
        ]);
        $process->start();

        while (!$process->getErrorOutput()) {
            usleep(50000);
        }

        $this->process = $process;
        $this->stream = stream_socket_client('tcp://' . $address, $errNo, $errString);
        if (!$this->stream) {
            throw new \Exception($errString);
        }
    }

    protected function process(): Process
    {
        return $this->process;
    }

    public function tearDown()
    {
        $this->process->stop(0);
    }

    public function input(): InputStream
    {
        return $this->input;
    }
}
