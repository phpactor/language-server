<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use ArrayIterator;
use IteratorAggregate;

class Handlers implements IteratorAggregate, HandlerRegistry
{
    private $handlers = [];

    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->add($handler);
        }
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        $names = [];
        foreach ($this->handlers as $handler) {
            $names[] = $handler->name();
        }

        return $names;
    }

    public function merge(Handlers $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function get(string $handler): Handler
    {
        if (!isset($this->handlers[$handler])) {
            throw new HandlerNotFound(sprintf(
                'Handler "%s" not found, available handlers: "%s"',
                $handler,
                implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$handler];
    }

    public function add(Handler $handler)
    {
        foreach (array_keys($handler->methods()) as $languageServerMethod) {
            $this->handlers[$languageServerMethod] = $handler;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->handlers);
    }
}
