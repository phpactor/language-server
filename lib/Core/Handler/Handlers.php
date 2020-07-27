<?php

namespace Phpactor\LanguageServer\Core\Handler;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Handler>
 */
final class Handlers implements Countable, IteratorAggregate
{
    /**
     * @var array
     */
    private $methods = [];

    public function __construct(Handler ...$handlers)
    {
        foreach ($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function get(string $handler): Handler
    {
        if (!isset($this->methods[$handler])) {
            throw new HandlerNotFound(sprintf(
                'Handler "%s" not found, available handlers: "%s"',
                $handler,
                implode('", "', array_keys($this->methods))
            ));
        }

        return $this->methods[$handler];
    }

    public function add(Handler $handler): void
    {
        foreach (array_keys($handler->methods()) as $languageServerMethod) {
            $this->methods[$languageServerMethod] = $handler;
        }
    }

    /**
     * @return ArrayIterator<string, Handler>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->methods);
    }

    public function merge(Handlers $handlers): void
    {
        foreach ($handlers->methods as $handler) {
            $this->add($handler);
        }
    }

    /**
     * @return Handler[]
     */
    public function methods(): array
    {
        return $this->methods;
    }

    public function count(): int
    {
        return count($this->methods);
    }
}
