<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\ByteStream\OutputStream;
use Amp\Promise;
use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageFormatter;

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
     * @var MessageFormatter
     */
    private $formatter;

    public function __construct(Dispatcher $innerDispatcher, OutputStream $output, MessageFormatter $formatter = null)
    {
        $this->output = $output;
        $this->innerDispatcher = $innerDispatcher;
        $this->formatter = $formatter ?: new MessageFormatter();
    }

    public function dispatch(Handlers $handlers, RequestMessage $request): Promise
    {
        \Amp\asyncCall(function () use ($request) {
            yield $this->output->write($this->formatter->write($request));
        });

        return $this->innerDispatcher->dispatch($handlers, $request);
    }
}
