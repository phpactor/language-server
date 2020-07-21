<?php

namespace Phpactor\LanguageServer\Core\Session;

use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Server\SessionServices;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

interface DispatcherFactory
{
    public function create(MessageTransmitter $transmitter): Dispatcher;
}
