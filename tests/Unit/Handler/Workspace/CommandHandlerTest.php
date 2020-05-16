<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\Workspace;

use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;

class CommandHandlerTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return $this->createHandler();
    }

    public function testExecutesCommand()
    {
        $result = $this->dispatch('workspace/executeCommand', [
            'command' => 'foobar',
            'arguments' => [
                'barfoo',
            ],
        ]);
        self::assertEquals('barfoo', $result->result);
    }

    public function testRegistersCapabilities()
    {
        $server = new ServerCapabilities();
        $this->createHandler()->registerCapabiltiies($server);

        /** @phpstan-ignore-next-line */
        self::assertEquals(['foobar'], $server->executeCommandProvider['commands']);
    }

    private function createHandler(): CommandHandler
    {
        return new CommandHandler(new CommandDispatcher([
            'foobar' => new class {
                public function __invoke(string $arg)
                {
                    return $arg;
                }
            }
        ]));
    }
}
