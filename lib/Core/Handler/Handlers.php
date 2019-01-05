<?php

namespace Phpactor\LanguageServer\Core\Handler;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Handlers implements Countable, IteratorAggregate
{
    private $handlers = [];

    public function __construct(array $handlers = [])
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

    public function merge(Handlers $handlers)
    {
        foreach ($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function methods(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->handlers);
    }
}
