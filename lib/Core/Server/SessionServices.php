<?php

namespace Phpactor\LanguageServer\Core\Server;

use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;

class SessionServices
{
    /**
     * @var MessageTransmitter
     */
    private $messageTransmitter;
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var ServerClient
     */
    private $client;

    public function __construct(MessageTransmitter $messageTransmitter, ServiceManager $serviceManager, ServerClient $client)
    {
        $this->messageTransmitter = $messageTransmitter;
        $this->serviceManager = $serviceManager;
        $this->client = $client;
    }

    public function serviceManager(): ServiceManager
    {
        return $this->serviceManager;
    }

    public function messageTransmitter(): MessageTransmitter
    {
        return $this->messageTransmitter;
    }

    public function client(): ServerClient
    {
        return $this->client;
    }
}
