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
    public function __construct(private Closure $factory)
    {
    }

    public function create(MessageTransmitter $transmitter, InitializeParams $initializeParams): Dispatcher
    {
        $dispatcher = $this->factory->__invoke($transmitter, $initializeParams);

        if (!$dispatcher instanceof Dispatcher) {
            throw new RuntimeException(sprintf(
                'Closure must return a "Dispatcher" instance got "%s"',
                get_debug_type($dispatcher)
            ));
        }

        return $dispatcher;
    }
}
