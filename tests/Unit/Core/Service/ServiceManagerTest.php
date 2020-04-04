<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Service;

use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\LanguageServer\Core\Handler\ServiceProvider;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Psr\Log\NullLogger;
use RuntimeException;

class ServiceManagerTest extends TestCase
{
    public function testStartServices()
    {
        $serviceManager = new ServiceManager(new NullMessageTransmitter(), new NullLogger());
        $service = new PingService();
        $serviceManager->register($service);
        $serviceManager->start();

        self::assertTrue($service->called);
    }

    public function testThrowExceptionIfServiceDoesNotHaveMethod()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service method');
        $serviceManager = new ServiceManager(new NullMessageTransmitter(), new NullLogger());
        $service = new PingServiceNoPromise();
        $serviceManager->register($service);
        $serviceManager->start();
        Loop::run();
    }

    public function testThrowExceptionIfServiceNotReturnPromise()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no service method');
        $serviceManager = new ServiceManager(new NullMessageTransmitter(), new NullLogger());
        $service = new PingServiceMissingMethod();
        $serviceManager->register($service);
        $serviceManager->start();
        Loop::run();
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
            'pingService',
        ];
    }

    public function pingService(): Promise
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
            'pingService',
        ];
    }

    public function pingService()
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
            'pingService',
        ];
    }
}
