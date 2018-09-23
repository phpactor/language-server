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

    public function __construct(LoggerInterface $logger, string $address)
    {
        $this->address = $address;
        $this->logger = $logger;

        $this->server = @stream_socket_server('tcp://' . $this->address, $errorNumber, $errorMessage);

        if ($errorMessage) {
            throw new RuntimeException(sprintf(
                'Could not create socket at %s: %s', $address, $errorMessage
            ));
        }

        $this->logger->info(sprintf('Listening on address %s', $this->address));
    }

    public function io(): IO
    {
        $socket = stream_socket_accept($this->server, -1);
        $this->logger->info('Connection accepted');
        stream_set_blocking($socket, 1);

        return new StreamIO($socket, $socket);
    }
}
