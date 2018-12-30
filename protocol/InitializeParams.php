<?php

namespace LanguageServerProtocol;

class InitializeParams
{
    /**
     * @var array
     */
    public $capabilities;

    /**
     * @var array
     */
    public $initializationOptions;

    /**
     * @var int|null
     */
    public $processId;

    /**
     * @var string|null
     */
    public $rootPath;

    /**
     * @var string
     */
    public $rootUri;

    /**
     * @var string
     */
    public $trace;

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
