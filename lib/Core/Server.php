<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Exception\ExitServer;
use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\RequestError;
use Phpactor\LanguageServer\Core\Exception\ShutdownServer;
use Phpactor\LanguageServer\Core\Protocol\LanguageServerProtocol;
use Phpactor\LanguageServer\Core\Serializer\JsonSerializer;
use Phpactor\LanguageServer\Core\Transport\RequestMessageFactory;
use Psr\Log\LoggerInterface;

class Server
{
    const SLEEP_INTERVAL_MICROSECONDS = 50000;
    const PROCESS_TITLE = 'Phpactor Language Server';


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
     * @var Protocol
     */
    private $protocol;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RequestMessageFactory
     */
    private $messageFactory;

    /**
     * @var Extension
     */
    private $extension;

    public function __construct(
        LoggerInterface $logger,
        Dispatcher $dispatcher,
        Connection $connection,
        Extension $extension,
        Protocol $protocol = null,
        Serializer $serializer = null,
        RequestMessageFactory $messageFactory = null
    ) {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->protocol = $protocol ?: LanguageServerProtocol::create($logger);
        $this->serializer = $serializer ?: new JsonSerializer();
        $this->messageFactory = $messageFactory ?: new RequestMessageFactory();
        $this->extension = $extension;
    }

    public function shutdown()
    {
        $this->logger->info('shutting down...');
        $this->connection->shutdown();
        throw new ExitServer();
    }

    public function start()
    {
        try {
            $this->doStart();
        } catch (ExitServer $e) {
            return;
        }
    }


    private function doStart()
    {
        $this->setProcessTitle();
        $this->registerSignalHandlers();
        $this->logger->info(sprintf('starting language server with pid: %s', getmypid()));
        $this->logger->debug(sprintf('handlers: %s', implode(', ', $this->extension->handlers()->names())));

        while ($io = $this->connection->accept()) {
            $this->logger->info('accepted connection');

            while (true) {
                try {
                    $this->dispatch($io);
                } catch (RequestError $e) {
                    $this->logger->error($e->getMessage());
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
        $request = $this->protocol->readRequest($io);

        $request = $this->serializer->deserialize($request->body());
        $request = $this->messageFactory->requestMessageFromArray($request);

        $responses = $this->dispatcher->dispatch($this->extension->handlers(), $request);

        foreach ($responses as $response) {
            $this->logger->debug('response', (array) $response);
            $this->protocol->writeResponse($io, $this->serializer->serialize((array) $response));
        }
    }

    private function registerSignalHandlers()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }

    private function setProcessTitle()
    {
        cli_set_process_title(self::PROCESS_TITLE);
    }
}
