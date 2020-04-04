<?php

namespace Phpactor\LanguageServer\Core\Handler;

use ArrayIterator;
use Countable;

final class Handlers implements Countable
{
    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $services = [];

    public function __construct(array $handlers = [])
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

        if ($handler instanceof ServiceProvider) {
            foreach (array_keys($handler->services()) as $serviceName) {
                $this->services[$serviceName] = $handler;
            }
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
        foreach (array_merge($handlers->methods, $handlers->services) as $handler) {
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
        return count($this->methods) + count($this->services);
    }

    /**
     * @return Handler[]
     */
    public function services(): array
    {
        return $this->services;
    }
}
