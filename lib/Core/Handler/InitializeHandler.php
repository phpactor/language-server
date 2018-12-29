<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Generator;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\InitializedParams;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;

class InitializeHandler implements Handler
{
    /**
     * @var EventEmitter
     */
    private $emitter;

    public function __construct(EventEmitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function methods(): array
    {
        return [
            'initialize' => 'initialize',
            'initialized' => 'initialized',
        ];
    }

    public function initialize(
        array $capabilities = [],
        array $initializationOptions = [],
        ?int $processId = null,
        ?string $rootPath = null,
        ?string $rootUri = null,
        ?string $trace = null
    ): Generator
    {
        $capabilities = new ServerCapabilities();

        $result = new InitializeResult();
        $result->capabilities = $capabilities;
        $this->emitter->emit(
            LanguageServerEvents::CAPABILITIES_REGISTER,
            [ $result ]
        );

        yield $result;
    }

    public function initialized()
    {
    }
}
