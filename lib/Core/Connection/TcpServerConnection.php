<?php

namespace Phpactor\LanguageServer\Core\Connection;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Connection;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Psr\Log\LoggerInterface;
use RuntimeException;

class TcpServerConnection implements Connection
{
    /**
     * @var string
     */
    private $address;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var resource
     */
    private $server;

    private $socket;

    public function __construct(LoggerInterface $logger, string $address)
    {
        $this->address = $address;
        $this->logger = $logger;

        $server = @stream_socket_server('tcp://' . $this->address, $errorNumber, $errorMessage);

        if (false === $server || $errorMessage) {
            throw new RuntimeException(sprintf(
                'Could not create socket at %s: %s',
                $address,
                $errorMessage
            ));
        }

        $this->server = $server;

        $this->logger->info(sprintf('listening on address %s', $this->address));
    }

    public function io(): IO
    {
        $socket = @stream_socket_accept($this->server, -1);

        if (false === $socket) {
            throw new RuntimeException(sprintf(
                'could not accept socket'
            ));
        }

        $this->logger->info('connection accepted');
        stream_set_blocking($socket, true);
        $this->socket = $socket;

        return new StreamIO($this->socket, $this->socket);
    }

    public function shutdown()
    {
        $this->logger->debug('closing socket stream', [
            'address' => $this->address
        ]);
        fclose($this->socket);
        fclose($this->server);
    }

    public function reset(): void
    {
        stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
    }
}
