<?php

namespace Phpactor\LanguageServer\Core\Handler;

use ArrayIterator;
use Countable;
use IteratorAggregate;

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
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->methods);
    }

    public function merge(Handlers $handlers)
    {
        foreach (array_merge($handlers->methods, $handlers->services) as $handler) {
            $this->add($handler);
        }
    }

    public function methods(): array
    {
        return array_keys($this->methods);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->methods);
    }

    public function services(): array
    {
        return $this->services;
    }
}
