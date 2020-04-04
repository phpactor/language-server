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
        LoggerInterface $logger
    ) {
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

            if (!method_exists($service, $serviceMethodName)) {
                throw new RuntimeException(sprintf(
                    'Handler "%s" has no service method "%s"',
                    get_class($service),
                    $serviceMethodName
                ));
            }

            \Amp\asyncCall(function () use ($service, $serviceMethodName) {
                $promise = $service->$serviceMethodName($this->transmitter);

                if (!$promise instanceof Promise) {
                    throw new RuntimeException(sprintf(
                        'Service method "%s" must return a Promise, got "%s"',
                        get_class($service) . '::' . $serviceMethodName,
                        is_object($promise) ? get_class($promise) : gettype($promise)
                    ));
                }

                yield $promise;
            });
        }
    }

    private function add(string $name, ServiceProvider $service): void
    {
        $this->services[$name] = $service;
    }
}
