<?php

namespace Phpactor\LanguageServer\Core;

use InvalidArgumentException;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Phpactor\LanguageServer\Core\ChunkIO;

class Server
{
    const CHUNK_SIZE = 100;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $stream;

    /**
     * @var ChunkReader
     */
    private $reader;

    public function __construct(
        LoggerInterface $logger,
        Dispatcher $dispatcher,
        ChunkIO $reader
    )
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->reader = $reader;
    }

    public function start()
    {
        [ $headers, $body ] = $this->readRequest();
        $request = $this->unserializeRequest($body);
        $response = $this->dispatcher->dispatch($request);
        $this->reader->write(json_encode($response));
    }

    private function readRequest()
    {
        $rawHeaders = [];
        $buffer = [];
        
        while ($chunk = $this->reader->read(self::CHUNK_SIZE)) {
            if (false === $chunk) {
                $buffer = [];
                usleep(50000);
            }
        
            foreach (str_split($chunk) as $char) {
                $buffer[] = $char;

                if (count($buffer) > 2 && array_slice($buffer, -2, 2) == [ "\r", "\n" ]) {
                    $header = trim(implode('', array_slice($buffer, 0, -2)));
        
                    if (!$header) {
                        continue;
                    }
        
                    $buffer = [];
                    $rawHeaders[] = $header;
                }
            }
        }

        $headers = $this->parseHeaders($rawHeaders);

        if (!array_key_exists('Content-Length', $headers)) {
            throw new RuntimeException(sprintf(
                'No valid Content-Length header provided in raw headers: "%s"',
                implode(', ', $rawHeaders)
            ));
        }

        $body = $this->reader->read($headers['Content-Length']);

        return [ $headers, trim(implode('', $buffer) . $body) ];
    }

    private function parseHeaders(array $rawHeaders): array
    {
        $parsed = [];
        foreach($rawHeaders as $keyValue) {
            $keyValue = explode(':', $keyValue);
            if (count($keyValue) != 2) {
                $this->logger->warning(sprintf('Invalid header "%s"', $keyValue));
                continue;
            }

            $parsed[$keyValue[0]] = trim($keyValue[1]);
        }

        return $parsed;
    }

    private function unserializeRequest(string $body)
    {
        $json = json_decode($body, true);

        if (false === $json) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON "%s" - "%s"',
                $body, json_last_error_msg()
            ));
        }

        $keys = [ 'jsonrpc', 'id', 'method', 'params' ];

        if ($diff = array_diff(array_keys($json), $keys)) {
            throw new RuntimeException(sprintf(
                'Request has invalid keys: "%s", valid keys: "%s"',
                implode(', ', $diff), implode(', ', $keys)
            ));
        }

        if ($diff = array_diff($keys, array_keys($json))) {
            throw new RuntimeException(sprintf(
                'Request is missing required keys: "%s"',
                implode(', ', $diff)
            ));
        }

        return new RequestMessage($json['id'], $json['method'], $json['params']);
    }
}
