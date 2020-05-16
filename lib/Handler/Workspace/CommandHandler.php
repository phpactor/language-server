<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Amp\Promise;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;

class CommandHandler implements Handler, CanRegisterCapabilities
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

    public function registerCapabiltiies(ServerCapabilities $capabilities)
    {
        // The protocol library is not up-to-date so
        // we are writing to undefined properties.
        // @phpstan-ignore-next-line
        $capabilities->executeCommandProvider = [
            'commands' => $this->dispatcher->registeredCommands(),
        ];
    }
}
