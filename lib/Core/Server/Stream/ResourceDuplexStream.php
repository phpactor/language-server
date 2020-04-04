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
}
