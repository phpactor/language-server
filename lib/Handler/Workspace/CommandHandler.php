<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\ExecuteCommandOptions;
use Phpactor\LanguageServerProtocol\ExecuteCommandParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;

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
    public function executeCommand(ExecuteCommandParams $params): Promise
    {
        return $this->dispatcher->dispatch($params->command, $params->arguments);
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->executeCommandProvider = new ExecuteCommandOptions(
            $this->dispatcher->registeredCommands(),
        );
    }
}
