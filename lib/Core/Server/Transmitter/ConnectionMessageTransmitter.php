<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Psr\Log\LoggerInterface;

final class ConnectionMessageTransmitter implements MessageTransmitter
{
    private const WRITE_CHUNK_SIZE = 256;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MessageFormatter
     */
    private $formatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Connection $connection, LoggerInterface $logger, MessageFormatter $formatter = null)
    {
        $this->connection = $connection;
        $this->formatter = $formatter ?: new LspMessageFormatter();
        $this->logger = $logger;
    }

    public function transmit(Message $response): void
    {
        $this->logger->info('OUT: ', (array) $response);

        $responseBody = $this->formatter->format($response);

        foreach (str_split($responseBody, self::WRITE_CHUNK_SIZE) as $chunk) {
            $this->connection->stream()->write($chunk);
        }
    }
}
