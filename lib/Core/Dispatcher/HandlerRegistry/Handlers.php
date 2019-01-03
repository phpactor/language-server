<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\HandlerRegistry;

use ArrayIterator;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerCollection;
use Phpactor\LanguageServer\Core\Dispatcher\HandlerNotFound;

class Handlers implements HandlerCollection
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
}
