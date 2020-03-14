<?php

namespace Phpactor\LanguageServer\Core\Service;

use Generator;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;

use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

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

    public function __construct(
        MessageTransmitter $transmitter,
        ?HandlerMethodResolver $methodResolver = null
    )
    {
        $this->methodResolver = $methodResolver ?: new HandlerMethodResolver();
        $this->transmitter = $transmitter;
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
            $method = $this->methodResolver->resolveHandlerMethod($service, $serviceMethodName);
            \Amp\asyncCall(function () use ($service, $method) {
                yield $service->$method($this->transmitter);
            });
        }
    }

    private function add($name, ServiceProvider $service)
    {
        $this->services[$name] = $service;
    }
}
