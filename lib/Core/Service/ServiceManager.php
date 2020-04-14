<?php

namespace Phpactor\LanguageServer\Core\Service;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
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

    /**
     * @var ArgumentResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $cancellations;

    public function __construct(
        MessageTransmitter $transmitter,
        LoggerInterface $logger,
        ArgumentResolver $resolver
    ) {
        $this->transmitter = $transmitter;
        $this->logger = $logger;
        $this->resolver = $resolver;
    }

    public function register(ServiceProvider $provider): void
    {
        foreach ($provider->services() as $serviceMethodName) {
            $this->add($serviceMethodName, $provider);
        }
    }

    /**
     * @return array<string>
     */
    public function runningServices(): array
    {
        return array_keys($this->services);
    }

    public function startAll(): void
    {
        foreach (array_keys($this->services) as $serviceMethodName) {
            $this->start($serviceMethodName);
        }
    }

    public function start(string $serviceName): void
    {
        $this->assertServiceExists($serviceName);

        $service = $this->services[$serviceName];

        if (isset($this->cancellations[$serviceName])) {
            throw new RuntimeException(sprintf(
                'Service "%s" is already running',
                $serviceName
            ));
        }

        $this->logger->info(sprintf('Starting service: %s (%s)', $serviceName, get_class($service)));
        
        if (!method_exists($service, $serviceName)) {
            throw new RuntimeException(sprintf(
                'Handler "%s" has no service method "%s"',
                get_class($service),
                $serviceName
            ));
        }
        
        $cancel = new CancellationTokenSource();
        $this->cancellations[$serviceName] = $cancel;
        $token = $cancel->getToken();

        \Amp\asyncCall(function () use ($service, $serviceName, $token) {
            $arguments = $this->resolver->resolveArguments(
                $service,
                $serviceName,
                [
                    '_transmitter' => $this->transmitter,
                    '_cancel' => $token,
                ]
            );

            $promise = $service->$serviceName(...$arguments);
        
            if (!$promise instanceof Promise) {
                throw new RuntimeException(sprintf(
                    'Service method "%s" must return a Promise, got "%s"',
                    get_class($service) . '::' . $serviceName,
                    is_object($promise) ? get_class($promise) : gettype($promise)
                ));
            }
        
            yield $promise;
        });
    }

    public function stop(string $serviceName): void
    {
        $this->assertServiceExists($serviceName);
        if (!isset($this->cancellations[$serviceName])) {
            throw new RuntimeException(sprintf(
                'Cannot stop service "%s" it has not been started, running services: "%s"',
                $serviceName,
                implode('", "', array_keys($this->cancellations))
            ));
        }

        $tokenSource = $this->cancellations[$serviceName];
        assert($tokenSource instanceof CancellationTokenSource);
        $tokenSource->cancel();
        unset($this->cancellations[$serviceName]);
    }

    private function add(string $name, ServiceProvider $service): void
    {
        $this->services[$name] = $service;
    }

    private function assertServiceExists(string $serviceName): void
    {
        if (!isset($this->services[$serviceName])) {
            throw new RuntimeException(sprintf(
                'Service "%s" not known, known services: "%s"',
                $serviceName,
                implode('", "', array_keys($this->services))
            ));
        }
    }
}
