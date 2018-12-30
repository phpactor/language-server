<?php

namespace Phpactor\LanguageServer\Core\Server\Stream;

use Amp\Promise;
use Amp\Socket\Socket;

class SocketDuplexStream implements DuplexStream
{
    /**
     * @var Socket
     */
    private $socket;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * {@inheritDoc}
     */
    public function read(): Promise
    {
        return $this->socket->read();
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $data): Promise
    {
        return $this->socket->write($data);
    }

    /**
     * {@inheritDoc}
     */
    public function end(string $finalData = ''): Promise
    {
        return $this->socket->end($finalData);
    }
}
