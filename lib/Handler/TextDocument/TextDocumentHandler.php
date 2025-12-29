<?php

namespace Phpactor\LanguageServer\Handler\TextDocument;

use Phpactor\LanguageServerProtocol\DidChangeTextDocumentParams;
use Phpactor\LanguageServerProtocol\DidCloseTextDocumentParams;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentParams;
use Phpactor\LanguageServerProtocol\DidSaveTextDocumentParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeFullEvent;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\TextDocumentSyncKind;
use Phpactor\LanguageServerProtocol\WillSaveTextDocumentParams;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TextDocumentHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @param TextDocumentSyncKind::* $syncKind
     */
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private int $syncKind = TextDocumentSyncKind::FULL,
    ) {
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

    public function didOpen(DidOpenTextDocumentParams $params): void
    {
        $this->dispatcher->dispatch(new TextDocumentOpened($params->textDocument));
    }

    public function didChange(DidChangeTextDocumentParams $params): void
    {
        $increments = [];
        foreach ($params->contentChanges as $contentChange) {
            if ($contentChange instanceof TextDocumentContentChangeIncrementalEvent) {
                $increments[] = $contentChange;
                continue;

            }
            if ($contentChange instanceof TextDocumentContentChangeFullEvent) {
                $this->dispatcher->dispatch(new TextDocumentUpdated($params->textDocument, $contentChange->text));
                continue;
            }
        }

        if ($increments === []) {
            return;
        }

        $this->dispatcher->dispatch(new TextDocumentIncrementallyUpdated(
            $params->textDocument,
            $increments,
        ));
    }

    public function didClose(DidCloseTextDocumentParams $params): void
    {
        $this->dispatcher->dispatch(new TextDocumentClosed($params->textDocument));
    }

    public function didSave(DidSaveTextDocumentParams $params): void
    {
        $this->dispatcher->dispatch(new TextDocumentSaved($params->textDocument, $params->text));
    }

    public function willSave(WillSaveTextDocumentParams $params): void
    {
    }

    public function willSaveWaitUntil(WillSaveTextDocumentParams $params): void
    {
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->textDocumentSync = $this->syncKind;
    }
}
