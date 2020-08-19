<?php

namespace Phpactor\LanguageServer\Core\Service;

use Amp\CancellationTokenSource;
use Amp\Promise;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use function Amp\asyncCall;

class ServiceManager
{

    /**
     * @var array
     */
    private $cancellations = [];

    /**
     * @var ServiceProviders
     */
    private $serviceProviders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ServiceProviders $serviceProviders,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->serviceProviders = $serviceProviders;
    }

    /**
     * @return array<string>
     */
    public function runningServices(): array
    {
        return array_keys($this->cancellations);
    }

    public function startAll(): void
    {
        foreach ($this->serviceProviders->names() as $serviceName) {
            $this->start($serviceName);
        }
    }

    public function start(string $serviceName): void
    {
        $provider = $this->serviceProviders->get($serviceName);

        if (isset($this->cancellations[$serviceName])) {
            throw new RuntimeException(sprintf(
                'Service "%s" is already running',
                $serviceName
            ));
        }

        $this->logger->info(sprintf('Starting service: %s (%s)', $serviceName, get_class($provider)));
        
        if (!method_exists($provider, $serviceName)) {
            throw new RuntimeException(sprintf(
                'Service provider "%s" has no service method "%s"',
                get_class($provider),
                $serviceName
            ));
        }
        
        $cancel = new CancellationTokenSource();
        $this->cancellations[$serviceName] = $cancel;
        $token = $cancel->getToken();

        asyncCall(function () use ($provider, $serviceName, $token) {
            $promise = $provider->$serviceName($token);
        
            if (!$promise instanceof Promise) {
                throw new RuntimeException(sprintf(
                    'Service method "%s" must return a Promise, got "%s"',
                    get_class($provider) . '::' . $serviceName,
                    is_object($promise) ? get_class($promise) : gettype($promise)
                ));
            }
        
            try {
                yield $promise;
            } catch (Throwable $error) {
                $this->logger->error(sprintf(
                    'Error in service "%s" "%s:%s": %s',
                    $serviceName,
                    get_class($provider),
                    __FUNCTION__,
                    $error->getMessage()
                ));
            }
        });
    }

    public function stop(string $serviceName): void
    {
        $this->serviceProviders->assertExists($serviceName);

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

    public function isRunning(string $serviceName): bool
    {
        $this->serviceProviders->assertExists($serviceName);

        return isset($this->cancellations[$serviceName]);
    }
}
