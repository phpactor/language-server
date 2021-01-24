<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher\Factory;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use RuntimeException;
use stdClass;

class ClosureDispatcherFactoryTest extends TestCase
{
    public function testReturnsDispatcher(): void
    {
        $dispatcher = $this->createDispatcherFactory(function () {
            return new ClosureDispatcher(function (): void {
            });
        })->create(new NullMessageTransmitter(), ProtocolFactory::initializeParams());

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    public function testExceptionIfNotReturningDispatcher(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createDispatcherFactory(function () {
            return new stdClass();
        })->create(new NullMessageTransmitter(), ProtocolFactory::initializeParams());
    }

    private function createDispatcherFactory(Closure $closure): ClosureDispatcherFactory
    {
        return new ClosureDispatcherFactory($closure);
    }
}
