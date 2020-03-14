<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use RuntimeException;

class HandlerMethodResolverTest extends TestCase
{
    /**
     * @var HandlerMethodResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->resolver = new HandlerMethodResolver();
    }

    public function testThrowsExceptionIfHandlerDidNotDeclaredMethods()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has not declared');

        $handler = new class implements Handler {
            public function methods():array {
                return [
                ];
            }
        };

        $this->resolver->resolveHandlerMethod($handler, ['bar'], 'foo');
    }

    public function testThrowsExceptionIfHandlerDoesNotHaveMethod()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not have');

        $handler = new class implements Handler {
            public function methods():array {
                return [
                ];
            }

            public function foo(): void {}
        };

        $this->resolver->resolveHandlerMethod($handler, ['foo' => 'boo'], 'foo');
    }

    public function testResolvesMethodName()
    {
        $handler = new class implements Handler {
            public function methods():array {
                return [
                ];
            }

            public function foo(): void {}
        };

        self::assertEquals('foo', $this->resolver->resolveHandlerMethod($handler, ['foo' => 'foo'], 'foo'));
    }
}
