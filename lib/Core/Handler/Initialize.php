<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\LanguageServerFactory;
use Phpactor\LanguageServer\Core\Session;
use Phpactor\LanguageServer\Core\SessionManager;
use RuntimeException;

class Initialize implements Handler
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'initialize';
    }

    public function __invoke(
        array $capabilities = [],
        array $initializationOptions = [],
        ?int $processId = null,
        ?string $rootPath = null,
        ?string $rootUri = null,
        ?string $trace = null
    ) {
        if (!$rootUri && $rootPath) {
            $rootUri = $rootPath;
        }

        if (!$rootUri) {
            throw new RuntimeException(
                'rootUri (or deprecated rootPath) must be specified'
            );
        }

        $this->sessionManager->initialize($rootUri, $processId);

        return new InitializeResult(new ServerCapabilities());
    }
}
