<?php

namespace Phpactor\LanguageServer\Core\Server\Response;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\StreamProvider\Connection;
use Psr\Log\LoggerInterface;

class ResponseTransmitter
{
    private const WRITE_CHUNK_SIZE = 256;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LanguageServerProtocolWriter
     */
    private $formatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Connection $connection, LoggerInterface $logger, LanguageServerProtocolWriter $formatter = null)
    {
        $this->connection = $connection;
        $this->formatter = $formatter ?: new LanguageServerProtocolWriter();
        $this->logger = $logger;
    }

    public function transmit(Message $response)
    {
        $this->logger->info('RESPONSE', (array) $response);

        $responseBody = $this->formatter->write($response);

        foreach (str_split($responseBody, self::WRITE_CHUNK_SIZE) as $chunk) {
            $this->connection->stream()->write($chunk);
        }
    }
}
