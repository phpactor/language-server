<?php

namespace Phpactor\LanguageServer\Core\Dispatcher\Factory;

use Closure;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory;
use RuntimeException;

final class ClosureDispatcherFactory implements DispatcherFactory
{
    /**
     * @var Closure
     */
    private $factory;

    public function __construct(Closure $factory)
    {
        $this->factory = $factory;
    }

    public function create(MessageTransmitter $transmitter, InitializeParams $initializeParams): Dispatcher
    {
        $dispatcher = $this->factory->__invoke($transmitter, $initializeParams);

        if (!$dispatcher instanceof Dispatcher) {
            throw new RuntimeException(sprintf(
                'Closure must return a "Dispatcher" instance got "%s"',
                is_object($dispatcher) ? get_class($dispatcher) : gettype($dispatcher)
            ));
        }

        return $dispatcher;
    }
}
