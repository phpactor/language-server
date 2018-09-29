<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\IterationLimitReached;
use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\Reader\LanguageServerProtocolReader;
use Phpactor\LanguageServer\Core\Reader\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Transport\RequestMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
     * @var Connection
     */
    private $connection;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var LanguageServerProtocolWriter
     */
    private $writer;

    public function __construct(
        LoggerInterface $logger,
        Dispatcher $dispatcher,
        Connection $connection,
        Reader $reader = null,
        LanguageServerProtocolWriter $writer = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->reader = $reader ?: new LanguageServerProtocolReader($logger);
        $this->writer = $writer ?: new LanguageServerProtocolWriter($logger);
    }

    public function shutdown()
    {
        $this->logger->info('Shutting down...');
        $this->connection->shutdown();
        exit(0);
    }

    public function start()
    {
        $this->registerSignalHandlers();
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
                } catch (ResetConnection $e) {
                    $this->logger->debug($e->getMessage());
                    $this->logger->info('Resetting connection...');
                    $this->connection->reset();
                    break 1;
                }
            }
        }
    }

    private function dispatch(IO $io)
    {
        $request = $this->reader->readRequest($io);
        $request = $this->unserializeRequest($request->body());
        $response = $this->dispatcher->dispatch($request);

        $this->logger->debug('response', (array) $response);

        $body = json_encode($response);

        if (false === $body) {
            throw new RuntimeException(
                'Could not encode response'
            );
        }

        $this->writer->writeResponse($io, $body);
    }

    private function unserializeRequest(string $body)
    {
        $json = json_decode($body, true);

        if (null === $json) {
            throw new ServerError(sprintf(
                'Could not decode JSON "%s" - "%s"',
                $body,
                json_last_error_msg()
            ));
        }

        $this->logger->debug('body', $json);

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

    private function registerSignalHandlers()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }
}
