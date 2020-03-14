<?php

namespace Phpactor\LanguageServer\Core\Service;

use Phpactor\LanguageServer\Core\Handler\ServiceProvider;

use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Psr\Log\LoggerInterface;

class ServiceManager
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @var HandlerMethodResolver
     */
    private $methodResolver;

    /**
     * @var MessageTransmitter
     */
    private $transmitter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MessageTransmitter $transmitter,
        LoggerInterface $logger,
        ?HandlerMethodResolver $methodResolver = null
    ) {
        $this->methodResolver = $methodResolver ?: new HandlerMethodResolver();
        $this->transmitter = $transmitter;
        $this->logger = $logger;
    }

    public function register(ServiceProvider $provider, array $services): void
    {
        foreach ($services as $serviceMethodName) {
            $this->add($serviceMethodName, $provider);
        }
    }

    public function start(): void
    {
        foreach ($this->services as $serviceMethodName => $service) {
            $this->logger->info(sprintf('Starting service: %s::%s', get_class($service), $serviceMethodName));
            $method = $this->methodResolver->resolveHandlerMethod($service, $service->services(), $serviceMethodName);
            \Amp\asyncCall(function () use ($service, $method) {
                yield from $service->$method($this->transmitter);
            });
        }
    }

    private function add($name, ServiceProvider $service)
    {
        $this->services[$name] = $service;
    }
}
