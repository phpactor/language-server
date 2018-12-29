<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use Generator;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\MessageType;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Extension;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use RuntimeException;

class InitializeParams
{
    /**
     * @var array
     */
    private $capabilities;

    /**
     * @var array
     */
    private $initializationOptions;

    /**
     * @var int|null
     */
    private $processId;

    /**
     * @var string|null
     */
    private $rootPath;

    /**
     * @var string
     */
    private $rootUri;

    /**
     * @var string
     */
    private $trace;

    public function __construct(
        array $capabilities = [],
        array $initializationOptions = [],
        ?int $processId = null,
        ?string $rootPath = null,
        ?string $rootUri = null,
        ?string $trace = null
    ) {
        $this->capabilities = $capabilities;
        $this->initializationOptions = $initializationOptions;
        $this->processId = $processId;
        $this->rootPath = $rootPath;
        $this->rootUri = $rootUri;
        $this->trace = $trace;
    }
}
