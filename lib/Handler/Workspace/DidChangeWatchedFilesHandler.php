<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Phpactor\LanguageServerProtocol\DidChangeTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesParams;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileSystemWatcher;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\FilesChanged;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Ramsey\Uuid\Uuid;
use function Amp\asyncCall;

class DidChangeWatchedFilesHandler implements Handler
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'workspace/didChangeWatchedFiles' => 'didChange'
        ];
    }

    public function didChange(DidChangeWatchedFilesParams $params)
    {
        $this->dispatcher->dispatch(new FilesChanged(...$params->changes));
    }
}
