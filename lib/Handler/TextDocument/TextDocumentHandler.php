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

final class TextDocumentHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
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
        $this->workspace->open($textDocument);
    }

    public function didChange(VersionedTextDocumentIdentifier $textDocument, array $contentChanges): void
    {
        foreach ($contentChanges as $contentChange) {
            $this->workspace->update(
                $textDocument,
                $contentChange['text']
            );
        }
    }

    public function didClose(TextDocumentIdentifier $textDocument): void
    {
        $this->workspace->remove(
            $textDocument
        );
    }

    public function didSave(TextDocumentIdentifier $textDocument, string $text = null): void
    {
        if ($text !== null) {
            $this->workspace->get($textDocument->uri)->text = $text;
        }
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
