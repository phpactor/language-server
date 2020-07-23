<?php

namespace Phpactor\LanguageServer\Core\Server\Initializer;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\Initializer;
use Phpactor\LanguageServer\Core\Server\Parser\RequestReader;




/**
 * Use pre-defined initialization parameters.
 * This is useful for testing.
 */
class PredefinedInitializer implements Initializer
{
    /**
     * @var InitializeParams
     */
    private $params;

    public function __construct(?InitializeParams $params = null)
    {
        $this->params = $params ?: new InitializeParams(new ClientCapabilities());
    }

    /**
     * {@inheritDoc}
     */
    public function provideInitializeParams(Message $message): InitializeParams
    {
        return $this->params;
    }
}
