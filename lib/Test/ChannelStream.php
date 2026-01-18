<?php

namespace Phpactor\LanguageServer\Test;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\IteratorStream;
use Amp\ByteStream\PendingReadError;
use Amp\ByteStream\StreamException;
use Amp\Emitter;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\Stream\DuplexStream;

final class ChannelStream implements DuplexStream
{
    private Emitter $emitter;

    private IteratorStream $stream;

    public function __construct()
    {
        $this->emitter = new Emitter();
        $this->stream = new IteratorStream($this->emitter->iterate());
    }

    /**
     * Reads data from the stream.
     *
     * @return Promise Resolves with a string when new data is available or `null` if the stream has closed.
     *
     * @psalm-return Promise<string|null>
     *
     * @throws PendingReadError Thrown if another read operation is still pending.
     */
    public function read(): Promise
    {
        return $this->stream->read();
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data Bytes to write.
     *
     * @return Promise Succeeds once the data has been successfully written to the stream.
     *
     * @throws ClosedException If the stream has already been closed.
     * @throws StreamException If writing to the stream fails.
     */
    public function write(string $data): Promise
    {
        return $this->emitter->emit($data);
    }

    /**
     * Marks the stream as no longer writable. Optionally writes a final data chunk before. Note that this is not the
     * same as forcefully closing the stream. This method waits for all pending writes to complete before closing the
     * stream. Socket streams implementing this interface should only close the writable side of the stream.
     *
     * @param string $finalData Bytes to write.
     *
     * @return Promise Succeeds once the data has been successfully written to the stream.
     *
     * @throws ClosedException If the stream has already been closed.
     * @throws StreamException If writing to the stream fails.
     */
    public function end(string $finalData = ''): Promise
    {
        $promise = $this->emitter->emit($finalData);
        $promise->onResolve(function (): void {
            $this->emitter->complete();
        });
        return $promise;
    }
}
