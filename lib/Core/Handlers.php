<?php

namespace Phpactor\LanguageServer\Core;

use Phpactor\LanguageServer\Core\Exception\HandlerNotFound;

class Handlers
{
    private $handlers = [];

    public function __construct(array $handlers)
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
        $this->handlers[$handler->name()] = $handler;
    }
}
