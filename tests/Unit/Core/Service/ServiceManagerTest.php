<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Service;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Exception;
use Generator;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\Test\TestLogger;
use RuntimeException;

class ServiceManagerTest extends AsyncTestCase
{
    use ProphecyTrait;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var TestLogger
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new TestLogger();
    }

    public function testStartAllServices(): void
    {
        $service = new PingService();
        $serviceManager = $this->createServiceManager($service);
        $serviceManager->startAll();

        self::assertTrue($service->called);
    }

    public function testStartService(): void
    {
        $service = new PingService();
        $serviceManager = $this->createServiceManager($service);
        self::assertFalse($service->called);
        $serviceManager->start('ping');
        self::assertTrue($serviceManager->isRunning('ping'));

        self::assertTrue($service->called);
    }

    public function testListRunningServices(): void
    {
        $service = new PingService();
        $serviceManager = $this->createServiceManager($service);
        self::assertEmpty($serviceManager->runningServices());
        $serviceManager->start('ping');
        self::assertEquals(['ping'], $serviceManager->runningServices());
        $serviceManager->stop('ping');
        self::assertEquals([], $serviceManager->runningServices());
    }

    public function testExceptionWhenTryingToStartRunningService(): void
    {
        $this->expectExceptionMessage('Service "ping" is already running');
        $service = new PingService();
        $serviceManager = $this->createServiceManager($service);
        $serviceManager->start('ping');
        $serviceManager->start('ping');
    }

    public function testHandlesExceptionsFromServices(): void
    {
        $service = new ExceptionThrowingService();
        $serviceManager = $this->createServiceManager($service);
        $serviceManager->start('exception');
        self::assertTrue($this->logger->hasErrorThatContains('No'));
    }

    public function testStopService(): Generator
    {
        $service = new DaemonService();
        $serviceManager = $this->createServiceManager($service);

        self::assertFalse($service->called);

        $serviceManager->start('daemon');

        yield \Amp\call(function () use ($serviceManager) {
            yield new Delayed(10);
            $serviceManager->stop('daemon');
            yield new Delayed(10);
        });

        self::assertTrue($service->called);
    }

    public function testExceptionOnNonExistingService(): void
    {
        $this->expectExceptionMessage('Service "daemon" not known, known services: "ping"');
        $service = new PingService();
        $serviceManager = $this->createServiceManager($service);
        self::assertFalse($service->called);
        $serviceManager->start('ping');
        $serviceManager->stop('daemon');
    }

    public function testThrowExceptionIfServiceDoesNotHaveMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service method');
        $service = new PingServiceNoPromise();
        $serviceManager = $this->createServiceManager($service);
        $serviceManager->startAll();
    }

    public function testThrowExceptionIfServiceNotReturnPromise(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no service method');
        $service = new PingServiceMissingMethod();
        $serviceManager = $this->createServiceManager($service);
        $serviceManager->startAll();
    }

    private function createServiceManager(ServiceProvider ...$services): ServiceManager
    {
        return new ServiceManager(new ServiceProviders(...$services), $this->logger);
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

class ExceptionThrowingService implements ServiceProvider
{
    public function methods(): array
    {
        return [];
    }

    public function services(): array
    {
        return [
            'exception',
        ];
    }

    public function exception(CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($cancel): void {
            throw new Exception('No');
        });
    }
}
