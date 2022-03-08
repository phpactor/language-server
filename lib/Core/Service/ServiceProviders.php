<?php

namespace Phpactor\LanguageServer\Core\Service;

use ArrayIterator;
use IteratorAggregate;

use Countable;
use Phpactor\LanguageServer\Core\Service\Exception\UnknownService;
use Traversable;

/**
 * @implements IteratorAggregate<ServiceProvider>
 */
final class ServiceProviders implements Countable, IteratorAggregate
{
    /**
     * @var array<string,ServiceProvider>
     */
    private $services = [];

    public function __construct(ServiceProvider ...$serviceProviders)
    {
        foreach ($serviceProviders as $serviceProvider) {
            foreach ($serviceProvider->services() as $methodName) {
                $this->services[$methodName] = $serviceProvider;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->services);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->services);
    }

    /**
     * @return array<string>
     */
    public function names(): array
    {
        return array_keys($this->services);
    }

    public function get(string $serviceName): ServiceProvider
    {
        $this->assertExists($serviceName);

        return $this->services[$serviceName];
    }

    public function assertExists(string $serviceName): void
    {
        if (!isset($this->services[$serviceName])) {
            throw new UnknownService(sprintf(
                'Service "%s" not known, known services: "%s"',
                $serviceName,
                implode('", "', array_keys($this->services))
            ));
        }
    }
}
