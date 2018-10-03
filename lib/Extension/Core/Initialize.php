<?php

namespace Phpactor\LanguageServer\Extension\Core;

use Generator;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\MessageType;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Extensions;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use RuntimeException;

class Initialize implements Handler
{
    /**
     * @var Manager
     */
    private $sessionManager;

    /**
     * @var Extensions
     */
    private $extensions;


    public function __construct(Extension $extensions, Manager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        $this->extensions = $extensions;
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
    ): Generator {
        if (!$rootUri && $rootPath) {
            $rootUri = $rootPath;
        }

        if (!$rootUri) {
            throw new RuntimeException(
                'rootUri (or deprecated rootPath) must be specified'
            );
        }

        $this->sessionManager->initialize($rootUri, $processId);

        $capabilities = new ServerCapabilities();
        $this->extensions->configureCapabilities($capabilities);

        yield new InitializeResult($capabilities);

        yield new NotificationMessage(
            'window/showMessage',
            [
                'type' => MessageType::INFO,
                'message' => 'Connected to the Phpactor Language Server'
            ]
        );
    }
}
