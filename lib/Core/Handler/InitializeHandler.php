<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Generator;
use LanguageServerProtocol\InitializeResult;
use LanguageServerProtocol\InitializedParams;
use LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Session\Manager;
use RuntimeException;

class InitializeHandler implements Handler
{
    /**
     * @var EventEmitter
     */
    private $emitter;

    /**
     * @var Manager
     */
    private $manager;

    public function __construct(EventEmitter $emitter, Manager $manager)
    {
        $this->emitter = $emitter;
        $this->manager = $manager;
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
        if (!$rootUri && $rootPath) {
            $rootUri = $rootPath;
        }

        if (!$rootUri) {
            throw new RuntimeException(
                'rootUri (or deprecated rootPath) must be specified'
            );
        }

        $this->manager->initialize($rootUri, $processId);
        yield $this->gatherServerCapabilities($capabilities);
    }

    public function initialized()
    {
    }

    private function gatherServerCapabilities(array $capabilities): InitializeResult
    {
        $capabilities = new ServerCapabilities();
        
        $result = new InitializeResult();
        $result->capabilities = $capabilities;
        $this->emitter->emit(
            LanguageServerEvents::CAPABILITIES_REGISTER,
            [ $result ]
        );
        return $result;
    }
}
