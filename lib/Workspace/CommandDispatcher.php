<?php

namespace Phpactor\LanguageServer\Workspace;

use RuntimeException;

class CommandDispatcher
{
    /**
     * @var array
     */
    private $commandMap = [];

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

    private function addCommand(string $id, object $invokable): void
    {
        if (!is_callable($invokable)) {
            throw new RuntimeException(sprintf(
                'Object "%s" is not invokable', get_class($invokable)
            ));
        }

        $this->commandMap[$id] = $invokable;
    }

    /**
     * @param array<int,mixed> $args
     *
     * @return mixed
     */
    public function dispatch(string $command, array $args = [])
    {
        if (!isset($this->commandMap[$command])) {
            throw new RuntimeException(sprintf(
                'Command "%s" not found, known commands: "%s"',
                $command, implode('", "', array_keys($this->commandMap))
            ));
        }

        return $this->commandMap[$command]->__invoke(...$args);
    }
}
