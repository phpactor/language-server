<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;

use Amp\ByteStream\OutputStream;
use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use RuntimeException;

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

    public function __construct(Dispatcher $innerDispatcher, OutputStream $output)
    {
        $this->output = $output;
        $this->innerDispatcher = $innerDispatcher;
    }

    public function dispatch(Handlers $handlers, RequestMessage $request): Generator
    {
        $json = json_encode($request);

        if (false === $json) {
            throw new RuntimeException(sprintf(
                'Could not serialize message: "%s"',
                json_last_error_msg()
            ));
        }

        \Amp\asyncCall(function () use ($json) {
            yield $this->output->write($json);
        });

        yield from $this->innerDispatcher->dispatch($handlers, $request);
    }
}
