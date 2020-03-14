<?php

namespace Phpactor\LanguageServer\Core\Session\Workspace;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use Phpactor\LanguageServer\Core\Session\Event\DocumentOpened;
use Phpactor\LanguageServer\Core\Session\Event\DocumentRemoved;
use Phpactor\LanguageServer\Core\Session\Event\DocumentUpdated;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Core\Session\WorkspaceEvents;

class EventEmittingWorkspace implements Workspace
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(Workspace $workspace, EmitterInterface $emitter)
    {
        $this->workspace = $workspace;
        $this->emitter = $emitter;
    }

    public function count(): int
    {
        return $this->workspace->count();
    }

    public function has(string $uri): bool
    {
        return $this->workspace->has($uri);
    }

    public function get(string $uri): TextDocumentItem
    {
        return $this->workspace->get($uri);
    }

    public function open(TextDocumentItem $textDocument): void
    {
        $this->workspace->open($textDocument);
        $this->emitter->emit(new DocumentOpened($textDocument));
    }

    public function update(VersionedTextDocumentIdentifier $textDocument, string $updatedText): void
    {
        $this->workspace->update($textDocument, $updatedText);
        $this->emitter->emit(new DocumentUpdated($textDocument, $updatedText));

    }

    public function openFiles(): int
    {
        return $this->workspace->openFiles();
    }

    public function remove(TextDocumentIdentifier $textDocument): void
    {
        $this->workspace->remove($textDocument);
        $this->emitter->emit(new DocumentRemoved($textDocument));
    }
}
