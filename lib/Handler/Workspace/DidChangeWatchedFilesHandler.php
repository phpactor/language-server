<?php

namespace Phpactor\LanguageServer\Handler\Workspace;

use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesParams;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Event\FilesChanged;
use Psr\EventDispatcher\EventDispatcherInterface;

class DidChangeWatchedFilesHandler implements Handler
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
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

    public function didChange(DidChangeWatchedFilesParams $params): void
    {
        $this->dispatcher->dispatch(new FilesChanged(...$params->changes));
    }
}
