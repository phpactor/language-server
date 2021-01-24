<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\Workspace;

use Amp\Success;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;

class CommandHandlerTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return $this->createHandler();
    }

    public function testExecutesCommand(): void
    {
        $result = $this->dispatch('workspace/executeCommand', [
            'command' => 'foobar',
            'arguments' => [
                'barfoo',
            ],
        ]);
        self::assertEquals('barfoo', $result->result);
    }

    public function testRegistersCapabilities(): void
    {
        $capabilities = new ServerCapabilities();
        $this->createHandler()->registerCapabiltiies($capabilities);

        self::assertEquals(['foobar'], $capabilities->executeCommandProvider->commands);
    }

    private function createHandler(): CommandHandler
    {
        return new CommandHandler(new CommandDispatcher([
            'foobar' => new class implements Command {
                public function __invoke(string $arg)
                {
                    return new Success($arg);
                }
            }
        ]));
    }
}
