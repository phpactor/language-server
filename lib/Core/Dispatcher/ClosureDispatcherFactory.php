<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Closure;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Session\DispatcherFactory;
use RuntimeException;

class ClosureDispatcherFactory implements DispatcherFactory
{
    /**
     * @var Closure
     */
    private $factory;

    public function __construct(Closure $factory)
    {
        $this->factory = $factory;
    }

    public function create(MessageTransmitter $transmitter): Dispatcher
    {
        $dispatcher = $this->factory->__invoke($transmitter);

        if (!$dispatcher instanceof Dispatcher) {
            throw new RuntimeException(sprintf(
                'Closure must return a "Dispatcher" instance got "%s"',
                is_object($dispatcher) ? get_class($dispatcher) : gettype($dispatcher)
            ));
        }

        return $dispatcher;
    }
}
