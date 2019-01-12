<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\ByteStream\OutputStream;
use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Writer\LanguageServerProtocolWriter;

class RecordingDispatcher implements Dispatcher
{
    /**
     * @var OutputStream
     */
    private $output;

    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    /**
     * @var LanguageServerProtocolWriter
     */
    private $writer;

    public function __construct(Dispatcher $innerDispatcher, OutputStream $output, LanguageServerProtocolWriter $writer = null)
    {
        $this->output = $output;
        $this->innerDispatcher = $innerDispatcher;
        $this->writer = $writer ?: new LanguageServerProtocolWriter();
    }

    public function dispatch(Handlers $handlers, RequestMessage $request): Generator
    {
        \Amp\asyncCall(function () use ($request) {
            yield $this->output->write($this->writer->write($request));
        });

        yield from $this->innerDispatcher->dispatch($handlers, $request);
    }
}
