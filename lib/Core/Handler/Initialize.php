<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\CompletionOptions;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\ServerFactory;
use Phpactor\LanguageServer\Core\Session;
use Phpactor\LanguageServer\Core\Transport\ResponseMessage;
use RuntimeException;

class Initialize implements Handler
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ServerFactory
     */
    private $serverFactory;

    public function __construct(Session $session, ServerFactory $serverFactory)
    {
        $this->session = $session;
        $this->serverFactory = $serverFactory;
    }

    public function name(): string
    {
        return 'initialize';
    }

    public function __invoke(
        array $capabilities = [],
        array $initializationOptions = [],
        string $processId = null,
        string $rootPath = null,
        string $rootUri = null,
        string $trace = null
    ) {
        if (!$rootUri && $rootPath) {
            $rootUri = $rootPath;
        }

        if (!$rootUri) {
            throw new RuntimeException(
                'rootUri (or deprecated rootPath) must be specified'
            );
        }

        $this->session->initialize($rootUri, $processId);
        $capabilities = $this->serverFactory->server()->capabilities();

        return new InitializeResult($capabilities);
    }
}
