<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;

class CommandHandler implements Handler
{
    /**
     * @var CommandDispatcher
     */
    private $dispatcher;

    public function __construct(CommandDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'workspace/executeCommand' => 'executeCommand',
        ];
    }

    /**
     * @return Promise<mixed|null>
     */
    public function executeCommand(string $command, array $arguments): Promise
    {
        return \Amp\call(function () use ($command, $arguments) {
            return $this->dispatcher->dispatch($command, $arguments);
        });
    }
}
