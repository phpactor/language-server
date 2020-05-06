<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use LanguageServerProtocol\ServerCapabilities;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\TextDocumentSyncKind;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TextDocumentHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function methods(): array
    {
        return [
            'textDocument/didOpen' => 'didOpen',
            'textDocument/didChange' => 'didChange',
            'textDocument/didClose' => 'didClose',
            'textDocument/didSave' => 'didSave',
            'textDocument/willSave' => 'willSave',
            'textDocument/willSaveWaitUntil' => 'willSaveWaitUntil',
        ];
    }

    public function didOpen(TextDocumentItem $textDocument): void
    {
        $this->dispatcher->dispatch(new TextDocumentOpened($textDocument));
    }

    public function didChange(VersionedTextDocumentIdentifier $textDocument, array $contentChanges): void
    {
        foreach ($contentChanges as $contentChange) {
            $this->dispatcher->dispatch(new TextDocumentUpdated($textDocument, $contentChange['text']));
        }
    }

    public function didClose(TextDocumentIdentifier $textDocument): void
    {
        $this->dispatcher->dispatch(new TextDocumentClosed($textDocument));
    }

    public function didSave(TextDocumentIdentifier $textDocument, string $text = null): void
    {
        $this->dispatcher->dispatch(new TextDocumentSaved($textDocument, $text));
    }

    public function willSave(TextDocumentIdentifier $identifier, int $reason): void
    {
    }

    public function willSaveWaitUntil(TextDocumentIdentifier $identifier, int $reason): void
    {
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->textDocumentSync = TextDocumentSyncKind::FULL;
    }
}
