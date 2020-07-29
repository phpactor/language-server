<?php

namespace Phpactor\LanguageServer\Core\Server\Stream;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\OutputStream;
use Amp\ByteStream\ResourceInputStream;
use Amp\Promise;

final class ResourceDuplexStream implements DuplexStream
{
    /**
     * @var InputStream
     */
    private $input;

    /**
     * @var OutputStream
     */
    private $output;

    public function __construct(InputStream $input, OutputStream $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * @return Promise<string|null>
     */
    public function read(): Promise
    {
        return $this->input->read();
    }

    /**
     * @return Promise<void>
     */
    public function write(string $data): Promise
    {
        return $this->output->write($data);
    }

    /**
     * @return Promise<void>
     */
    public function end(string $finalData = ''): Promise
    {
        return $this->output->end($finalData);
    }

    public function close(): void
    {
        if ($this->input instanceof ResourceInputStream) {
            $this->input->close();
        }
    }
}
