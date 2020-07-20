<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\NullMessageTransmitter;
use RuntimeException;

class ClosureDispatcherFactoryTest extends TestCase
{
    public function testReturnsDispatcher(): void
    {
        $dispatcher = $this->createDispatcherFactory(function () {
            return new ClosureDispatcher(function () {});
        })->create(new NullMessageTransmitter());

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    public function testExceptionIfNotReturningDispatcher(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createDispatcherFactory(function () {
            return new \stdClass();
        })->create(new NullMessageTransmitter());
    }

    private function createDispatcherFactory(Closure $closure): ClosureDispatcherFactory
    {
        return new ClosureDispatcherFactory($closure);
    }
    
}
