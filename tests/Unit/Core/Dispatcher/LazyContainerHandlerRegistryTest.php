<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Dispatcher;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;
use Phpactor\LanguageServer\Core\Dispatcher\LazyContainerHandlerRegistry;
use Phpactor\LanguageServer\Core\Session\Session;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use Psr\Container\ContainerInterface;

class LazyContainerHandlerRegistryTest extends TestCase
{
    public function testReturnsServiceUsingSessionContainer()
    {
        $handler = $this->prophesize(Handler::class);
        $manager = new SessionManager();
        $container = new PhpactorContainer();
        $container->register('services.my_method.handler', function (Container $container) use ($handler) {
            return $handler->reveal();
        });
        $manager->load(new Session(__DIR__, 1, $container));

        $registry = new LazyContainerHandlerRegistry($manager, [
            'my/method' => function (ContainerInterface $container) {
                return $container->get('services.my_method.handler');
            },
        ]);

        $handler = $registry->get('my/method');
        $this->assertInstanceOf(Handler::class, $handler);
    }

    public function testThrowsExceptionWhenNoFactoryExists()
    {
        $this->expectException(HandlerNotFound::class);

        $manager = new SessionManager();
        $registry = new LazyContainerHandlerRegistry($manager, []);
        $manager->load(new Session(__DIR__, 1));

        $handler = $registry->get('my/method');
        $this->assertInstanceOf(Handler::class, $handler);
    }
}
