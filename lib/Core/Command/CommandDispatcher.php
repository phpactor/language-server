<?php

namespace Phpactor\LanguageServer\Core\Command;

use Amp\Promise;
use RuntimeException;

/**
 * Commands can be registered using this class.
 */
class CommandDispatcher
{
    /**
     * @var array
     */
    private $commandMap = [];

    /**
     * @param array<string,Command> $commandMap Map of command names to invokable objects
     */
    public function __construct(array $commandMap)
    {
        foreach ($commandMap as $id => $command) {
            $this->addCommand($id, $command);
        }
    }

    /**
     * @return array<string>
     */
    public function registeredCommands(): array
    {
        return array_keys($this->commandMap);
    }

    /**
     * @param array<int,mixed> $args
     *
     * @return Promise<mixed>
     */
    public function dispatch(string $command, array $args = []): Promise
    {
        if (!isset($this->commandMap[$command])) {
            throw new RuntimeException(sprintf(
                'Command "%s" not found, known commands: "%s"',
                $command,
                implode('", "', array_keys($this->commandMap))
            ));
        }

        return $this->commandMap[$command]->__invoke(...$args);
    }

    private function addCommand(string $id, Command $invokable): void
    {
        if (!is_callable($invokable)) {
            throw new RuntimeException(sprintf(
                'Object "%s" is not invokable',
                get_class($invokable)
            ));
        }

        $this->commandMap[$id] = $invokable;
    }
}
