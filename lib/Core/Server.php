<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Exception\IterationLimitReached;
use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Connection;

class Server
{
    const SLEEP_INTERVAL_MICROSECONDS = 50000;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $cycleLimit;

    /**
     * @var int
     */
    private $cycleCount = 0;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        LoggerInterface $logger,
        Dispatcher $dispatcher,
        Connection $connection,
        ?int $cycleLimit = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->cycleLimit = $cycleLimit;
        $this->connection = $connection;
    }

    public function start()
    {
        $this->logger->info(sprintf('Starting Language Server PID: %s', getmypid()));

        while ($io = $this->connection->io()) {
            $this->logger->info('Accepted connection');
            while (true) {
                try {
                    $this->dispatch($io);
                } catch (ServerError $e) {
                    $this->logger->error($e->getMessage());
                } catch (IterationLimitReached $e) {
                    $this->logger->info($e->getMessage());
                    break 2;
                }
            }
        }
    }

    private function dispatch(IO $io)
    {
        [ $headers, $body ] = $this->readRequest($io);
        $this->logger->debug('headers', $headers);
        $request = $this->unserializeRequest($body);
        $response = $this->dispatcher->dispatch($request);
        $this->logger->debug('response', (array) $response);

        $body = json_encode($response);
        $length = mb_strlen($body);
        $io->write("Content-Length:{$length}\r\n\r\n{$body}");
    }

    private function readRequest(IO $io)
    {
        $rawHeaders = [];
        $buffer = [];
        
        while (true) {
            $chunk = $io->read(1);

            if (false === $chunk->hasContents()) {
                if (null !== $this->cycleLimit && $this->cycleCount++ == $this->cycleLimit) {
                    throw new IterationLimitReached(sprintf(
                        'Iteration limit of "%s" reached for server',
                        $this->cycleLimit
                    ));
                }

                $buffer = [];
                usleep(self::SLEEP_INTERVAL_MICROSECONDS);
                continue;
            }

            $buffer[] = $chunk->contents();

            if (count($buffer) >= 2 && array_slice($buffer, -2, 2) == [ "\r", "\n" ]) {
                $header = trim(implode('', array_slice($buffer, 0, -2)));
    
                if (!$header) {
                    break;
                }
    
                $buffer = [];
                $rawHeaders[] = $header;
            }
        }

        $headers = $this->parseHeaders($rawHeaders);

        if (!array_key_exists('Content-Length', $headers)) {
            throw new ServerError(sprintf(
                'No valid Content-Length header provided in raw headers: "%s"',
                implode(', ', $rawHeaders)
            ));
        }

        $length = (int) $headers['Content-Length'];
        if ($length < 1) {
            throw new ServerError(sprintf(
                'Content length must be greater than 0, got: %s', $length
            ));
        }

        $body = $io->read($length);

        if (false === $body->hasContents()) {
            throw new ServerError('No contents read from stream');
        }

        return [ $headers, $body->contents() ];
    }

    private function parseHeaders(array $rawHeaders): array
    {
        $parsed = [];
        foreach ($rawHeaders as $rawHeader) {
            $keyValue = explode(':', $rawHeader);
            if (count($keyValue) != 2) {
                $this->logger->warning(sprintf('Invalid header "%s"', $rawHeader));
                continue;
            }

            $parsed[$keyValue[0]] = trim($keyValue[1]);
        }

        return $parsed;
    }

    private function unserializeRequest(string $body)
    {
        $json = json_decode($body, true);
        $this->logger->debug('body', $json);

        if (null === $json) {
            throw new ServerError(sprintf(
                'Could not decode JSON "%s" - "%s"',
                $body,
                json_last_error_msg()
            ));
        }

        $keys = [ 'jsonrpc', 'id', 'method', 'params' ];

        if ($diff = array_diff(array_keys($json), $keys)) {
            throw new ServerError(sprintf(
                'Request has invalid keys: "%s", valid keys: "%s"',
                implode(', ', $diff),
                implode(', ', $keys)
            ));
        }

        $json = array_merge([
            'id' => null
        ], $json);

        if ($diff = array_diff($keys, array_keys($json))) {
            throw new ServerError(sprintf(
                'Request is missing required keys: "%s"',
                implode(', ', $diff)
            ));
        }


        return new RequestMessage((int) $json['id'], $json['method'], $json['params']);
    }
}
