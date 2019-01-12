<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Dispatcher;

use Amp\ByteStream\OutputBuffer;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\RecordingDispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

class RecordingDispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerDispatcher;

    /**
     * @var RecordingDispatcher
     */
    private $dispatcher;

    /**
     * @var OutputBuffer
     */
    private $output;

    public function setUp()
    {
        $this->innerDispatcher = $this->prophesize(Dispatcher::class);
        $this->output = new OutputBuffer();

        $this->dispatcher = new RecordingDispatcher(
            $this->innerDispatcher->reveal(),
            $this->output
        );
    }

    public function testRecordsToOutputStream()
    {
        $message = new RequestMessage(1, 'hello', []);
        $handlers = new Handlers([]);

        $this->innerDispatcher->dispatch($handlers, $message)->will(function () {
            yield null;
        });

        iterator_to_array($this->dispatcher->dispatch($handlers, $message));
        $this->output->end();

        $this->assertContains('{"id":1,"method":"hello","params":[],"jsonrpc":"2.0"}', \Amp\Promise\wait($this->output));
    }
}
