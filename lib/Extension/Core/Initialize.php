<?php

namespace Phpactor\LanguageServer\Extension\Core;

use Generator;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\MessageType;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use RuntimeException;

class Initialize implements Handler
{
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

        $capabilities = new ServerCapabilities();

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
