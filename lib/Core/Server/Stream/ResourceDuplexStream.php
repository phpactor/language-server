<?php

namespace Phpactor\LanguageServer\Core\Server\Stream;

use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Promise;

class ResourceDuplexStream implements DuplexStream
{
    /**
     * @var ResourceInputStream
     */
    private $input;

    /**
     * @var ResourceOutputStream
     */
    private $output;

    public function __construct(ResourceInputStream $input, ResourceOutputStream $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function read(): Promise
    {
        return $this->input->read();
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $data): Promise
    {
        return $this->output->write($data);
    }

    /**
     * {@inheritDoc}
     */
    public function end(string $finalData = ''): Promise
    {
        return $this->output->end($finalData);
    }
}
