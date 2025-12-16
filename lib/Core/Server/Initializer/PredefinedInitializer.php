<?php

namespace Phpactor\LanguageServer\Core\Server\Initializer;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\Initializer;

/**
 * Use pre-defined initialization parameters.
 * This is useful for testing.
 */
final class PredefinedInitializer implements Initializer
{
    public function __construct(private InitializeParams $params = new InitializeParams(new ClientCapabilities()))
    {
    }

    /**
     * {@inheritDoc}
     */
    public function provideInitializeParams(Message $message): InitializeParams
    {
        return $this->params;
    }
}
