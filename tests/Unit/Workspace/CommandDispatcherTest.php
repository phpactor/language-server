<?php

namespace Phpactor\LanguageServer\Tests\Unit\Workspace;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;
use RuntimeException;

class CommandDispatcherTest extends TestCase
{
    public function testDispatchesRequest(): void
    {
        $result = $this->createDispatcher([
            'foobar' => new class {
                public function __invoke(string $foobar)
                {
                    return $foobar;
                }
            }
        ])->dispatch('foobar', [
            'barfoo',
        ]);

        self::assertEquals('barfoo', $result);
    }

    public function testExceptionWhenCommandNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Command "barfoo" not found');

        $this->createDispatcher([
            'foobar' => new class {
                public function __invoke(string $foobar)
                {
                }
            }
        ])->dispatch('barfoo');
    }

    public function testExceptionWhenCommandNotInvokable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not invokable');
        $this->createDispatcher([
            'command' => new \stdClass(),
        ]);
    }

    public function testReturnsRegisteredCommands(): void
    {
        $result = $this->createDispatcher([
            'foobar' => new class {
                public function __invoke(string $foobar)
                {
                    return $foobar;
                }
            },
            'barfoo' => new class {
                public function __invoke(string $foobar)
                {
                    return $foobar;
                }
            }
        ]);

        self::assertEquals(['foobar','barfoo'], $result->registeredCommands());
    }

    private function createDispatcher(array $map): CommandDispatcher
    {
        return new CommandDispatcher($map);
    }
}
