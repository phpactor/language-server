<?php

namespace Phpactor\LanguageServer\Core\Service;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;

use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Psr\Log\LoggerInterface;
use RuntimeException;

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

    public function register(ServiceProvider $provider): void
    {
        foreach ($provider->services() as $serviceMethodName) {
            $this->add($serviceMethodName, $provider);
        }
    }

    public function start(): void
    {
        foreach ($this->services as $serviceMethodName => $service) {
            $this->logger->info(sprintf('Starting service: %s::%s', get_class($service), $serviceMethodName));
            $method = $this->methodResolver->resolveHandlerMethod($service, $service->services(), $serviceMethodName);
            \Amp\asyncCall(function () use ($service, $method) {
                $promise = $service->$method($this->transmitter);

                if (!$promise instanceof Promise) {
                    throw new RuntimeException(sprintf(
                        'Service method "%s" must return a Promise, got "%s"',
                        get_class($service) . '::' . $method,
                        is_object($promise) ? get_class($promise) : gettype($promise)
                    ));
                }

                yield $promise;
            });
        }
    }

    private function add($name, ServiceProvider $service)
    {
        $this->services[$name] = $service;
    }
}
