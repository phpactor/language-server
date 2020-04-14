<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Service;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Psr\Log\NullLogger;
use RuntimeException;

class ServiceManagerTest extends AsyncTestCase
{
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->argumentResolver = new DTLArgumentResolver();
    }

    public function testStartAllServices()
    {
        $serviceManager = $this->createServiceManager();
        $service = new PingService();
        $serviceManager->register($service);
        $serviceManager->startAll();

        self::assertTrue($service->called);
    }

    public function testStartService()
    {
        $serviceManager = $this->createServiceManager();
        $service = new PingService();
        $serviceManager->register($service);
        self::assertFalse($service->called);
        $serviceManager->start('ping');

        self::assertTrue($service->called);
    }

    public function testExceptionWhenTryingToStartRunningService()
    {
        $this->expectExceptionMessage('Service "ping" is already running');
        $serviceManager = $this->createServiceManager();
        $service = new PingService();
        $serviceManager->register($service);
        $serviceManager->start('ping');
        $serviceManager->start('ping');
    }

    public function testStopService()
    {
        $serviceManager = $this->createServiceManager();
        $service = new DaemonService();
        $serviceManager->register($service);
        self::assertFalse($service->called);
        $serviceManager->start('daemon');

        yield \Amp\call(function () use ($serviceManager) {
            yield new Delayed(10);
            $serviceManager->stop('daemon');
            yield new Delayed(10);
        });

        self::assertTrue($service->called);
    }

    public function testExceptionOnNonExistingService()
    {
        $this->expectExceptionMessage('Service "daemon" not known, known services: "ping"');
        $serviceManager = $this->createServiceManager();
        $service = new PingService();
        $serviceManager->register($service);
        self::assertFalse($service->called);
        $serviceManager->start('ping');
        $serviceManager->stop('daemon');
    }

    public function testThrowExceptionIfServiceDoesNotHaveMethod()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service method');
        $serviceManager = $this->createServiceManager();
        $service = new PingServiceNoPromise();
        $serviceManager->register($service);
        $serviceManager->startAll();
    }

    public function testThrowExceptionIfServiceNotReturnPromise()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no service method');
        $serviceManager = $this->createServiceManager();
        $service = new PingServiceMissingMethod();
        $serviceManager->register($service);
        $serviceManager->startAll();
    }

    private function createServiceManager(): ServiceManager
    {
        return new ServiceManager(new NullMessageTransmitter(), new NullLogger(), $this->argumentResolver);
    }
}

class PingService implements ServiceProvider
{
    public $called = false;
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping',
        ];
    }

    public function ping(): Promise
    {
        $this->called = true;
        return new Success();
    }
}

class PingServiceNoPromise implements ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping',
        ];
    }

    public function ping()
    {
        return 'asd';
    }
}

class PingServiceMissingMethod implements ServiceProvider
{
    public $called = false;
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'ping',
        ];
    }
}

class DaemonService implements ServiceProvider
{
    public $called = false;
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'daemon',
        ];
    }

    public function daemon(CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($cancel) {
            while (true) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    break;
                }
                yield new Delayed(1);
            }
            $this->called = true;
            return null;
        });
    }
}
