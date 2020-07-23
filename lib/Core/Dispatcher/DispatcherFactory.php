<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher;
use Phpactor\LanguageServer\Core\Server\SessionServices;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;
use Psr\Log\LoggerInterface;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;

interface DispatcherFactory
{
    public function create(MessageTransmitter $transmitter, InitializeParams $initializeParams): Dispatcher;
}
