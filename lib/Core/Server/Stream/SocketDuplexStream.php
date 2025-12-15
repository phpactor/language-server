<?php

namespace Phpactor\LanguageServer\Core\Server\Stream;

use Amp\Promise;
use Amp\Socket\Socket;

final class SocketDuplexStream implements DuplexStream
{
    public function __construct(private Socket $socket)
    {
    }

    /**
     * @return Promise<string|null>
     */
    public function read(): Promise
    {
        return $this->socket->read();
    }

    /**
     * @return Promise<void>
     */
    public function write(string $data): Promise
    {
        return $this->socket->write($data);
    }

    /**
     * @return Promise<void>
     */
    public function end(string $finalData = ''): Promise
    {
        return $this->socket->end($finalData);
    }
}
