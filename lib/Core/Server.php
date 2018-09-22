<?php

namespace Phpactor\LanguageServer\Core;

use Exception;
use InvalidArgumentException;
use Phpactor\LanguageServer\Core\Dispatcher;
use Phpactor\LanguageServer\Core\Exception\IterationLimitReached;
use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Phpactor\LanguageServer\Core\ChunkIO;

class Server
{
    const CHUNK_SIZE = 100;
    const SLEEP_INTERVAL_MICROSECONDS = 50000;


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
     * @var ChunkIO
     */
    private $chunkIO;

    /**
     * @var int
     */
    private $iterations;

    private $cycleCount = 0;

    public function __construct(
        LoggerInterface $logger,
        Dispatcher $dispatcher,
        ChunkIO $chunkIO,
        ?int $iterations = null
    )
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->chunkIO = $chunkIO;
        $this->iterations = $iterations;
    }

    public function start()
    {
        while (true) {
            try {
                $this->dispatch();
            } catch (ServerError $e) {
                $this->logger->error($e->getMessage());
            } catch (IterationLimitReached $e) {
                $this->logger->info($e->getMessage());
                break;
            }
        }
    }

    private function dispatch()
    {
        [ $headers, $body ] = $this->readRequest();
        $request = $this->unserializeRequest($body);
        $response = $this->dispatcher->dispatch($request);

        $this->chunkIO->write(json_encode($response));
    }

    private function readRequest()
    {
        $rawHeaders = [];
        $buffer = [];
        
        while ($chunk = $this->chunkIO->read(self::CHUNK_SIZE)) {

            if (false === $chunk->hasContents()) {

                if (null !== $this->iterations && $this->cycleCount++ == $this->iterations) {
                    throw new IterationLimitReached(sprintf(
                        'Iteration limit of "%s" reached for server', $this->iterations
                    ));
                }

                $buffer = [];

                usleep(self::SLEEP_INTERVAL_MICROSECONDS);
                continue;
            }

            $escape = false;
            foreach (str_split($chunk->contents()) as $char) {
                $buffer[] = $char;

                if (count($buffer) > 2 && array_slice($buffer, -2, 2) == [ "\r", "\n" ]) {
                    $header = trim(implode('', array_slice($buffer, 0, -2)));
        
                    if (!$header) {
                        $escape = true;
                        continue;
                    }
        
                    $buffer = [];
                    $rawHeaders[] = $header;
                }
            }

            if ($escape) {
                break;
            }
        }

        $headers = $this->parseHeaders($rawHeaders);

        if (!array_key_exists('Content-Length', $headers)) {
            throw new ServerError(sprintf(
                'No valid Content-Length header provided in raw headers: "%s"',
                implode(', ', $rawHeaders)
            ));
        }

        $body = $this->chunkIO->read($headers['Content-Length']);

        return [ $headers, trim(implode('', $buffer) . $body->contents()) ];
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
            throw new ServerError(sprintf(
                'Could not decode JSON "%s" - "%s"',
                $body, json_last_error_msg()
            ));
        }

        $keys = [ 'jsonrpc', 'id', 'method', 'params' ];

        if ($diff = array_diff(array_keys($json), $keys)) {
            throw new ServerError(sprintf(
                'Request has invalid keys: "%s", valid keys: "%s"',
                implode(', ', $diff), implode(', ', $keys)
            ));
        }

        if ($diff = array_diff($keys, array_keys($json))) {
            throw new ServerError(sprintf(
                'Request is missing required keys: "%s"',
                implode(', ', $diff)
            ));
        }

        return new RequestMessage($json['id'], $json['method'], $json['params']);
    }
}
