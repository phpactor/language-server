<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\Workspace;

use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Tests\Unit\Handler\HandlerTestCase;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;

class CommandHandlerTest extends HandlerTestCase
{
    public function handler(): Handler
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

    public function testExecutesCommand()
    {
        $result = $this->dispatch('workspace/executeCommand', [
            'command' => 'foobar',
            'arguments' => [
                'barfoo',
            ],
        ]);
    }
}
