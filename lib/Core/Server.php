<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\IterationLimitReached;
use Phpactor\LanguageServer\Core\Exception\RequestError;
use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Reader\LanguageServerProtocolReader;
use Phpactor\LanguageServer\Core\Reader\LanguageServerProtocolWriter;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;
use Phpactor\LanguageServer\Core\Transport\RequestMessageFactory;
use Psr\Log\LoggerInterface;

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

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var RequestMessageFactory
     */
    private $messageFactory;

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
        $this->serializer = new JsonSerializer();
        $this->messageFactory = new RequestMessageFactory();
    }

    public function shutdown()
    {
        $this->logger->info('shutting down...');
        $this->connection->shutdown();
        exit(0);
    }

    public function start()
    {
        $this->registerSignalHandlers();
        $this->logger->info(sprintf('starting language server with pid: %s', getmypid()));

        while ($io = $this->connection->io()) {
            $this->logger->info('accepted connection');

            while (true) {
                try {
                    $this->dispatch($io);
                } catch (RequestError $e) {
                    $this->logger->error($e->getMessage());
                } catch (IterationLimitReached $e) {
                    $this->logger->info($e->getMessage());
                    break 2;
                } catch (ResetConnection $e) {
                    $this->logger->debug($e->getMessage());
                    $this->logger->info('resetting connection...');
                    $this->connection->reset();
                    break 1;
                } catch (ShutdownServer $e) {
                    $this->shutdown();
                }
            }
        }
    }

    private function dispatch(IO $io)
    {
        $request = $this->reader->readRequest($io);
        $this->logger->debug($request->body());
        $request = $this->serializer->deserialize($request->body());
        $request = $this->messageFactory->requestMessageFromArray($request);

        $responses = $this->dispatcher->dispatch($request);

        foreach ($responses as $response) {
            $this->logger->debug('response', (array) $response);
            $this->writer->writeResponse($io, $this->serializer->serialize((array) $response));
        }
    }

    private function registerSignalHandlers()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }
}
