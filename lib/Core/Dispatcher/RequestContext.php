<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Amp\CancellationToken;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Server\Transmitter\ConnectionMessageTransmitter;

class RequestContext
{
    /**
     * @var Handlers
     */
    private $handlers;

    /**
     * @var CancellationToken
     */
    private $token;

    /**
     * @var ConnectionMessageTransmitter
     */
    private $transmitter;

    public function __construct(Handlers $handlers, CancellationToken $token, ConnectionMessageTransmitter $transmitter)
    {
        $this->handlers = $handlers;
        $this->token = $token;
        $this->transmitter = $transmitter;
    }

    public function handlers(): Handlers
    {
        return $this->handlers;
    }

    public function token(): CancellationToken
    {
        return $this->token;
    }

    public function transmitter(): ConnectionMessageTransmitter
    {
        return $this->transmitter;
    }
}
