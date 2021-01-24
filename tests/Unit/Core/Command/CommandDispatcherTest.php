<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Command;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use RuntimeException;

class CommandDispatcherTest extends TestCase
{
    public function testDispatchesRequest(): void
    {
        $result = $this->createDispatcher([
            'foobar' => new class implements Command {
                public function __invoke(string $foobar)
                {
                    return new Success($foobar);
                }
            }
        ])->dispatch('foobar', [
            'barfoo',
        ]);

        self::assertEquals('barfoo', \Amp\Promise\wait($result));
    }

    public function testExceptionWhenCommandNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Command "barfoo" not found');

        $this->createDispatcher([
            'foobar' => new class implements Command {
                public function __invoke(string $foobar): void
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
            'command' => new class implements Command {
            },
        ]);
    }

    public function testReturnsRegisteredCommands(): void
    {
        $result = $this->createDispatcher([
            'foobar' => new class implements Command {
                public function __invoke(string $foobar)
                {
                    return $foobar;
                }
            },
            'barfoo' => new class implements Command {
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
