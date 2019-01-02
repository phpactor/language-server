<?php

namespace Phpactor\LanguageServer\Core\Handler;

use LanguageServerProtocol\DidSaveTextDocumentParams;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Session\SessionManager;

final class TextDocumentHandler implements Handler
{
    /**
     * @var SessionManager
     */
    private $manager;

    /**
     * @var EventEmitter
     */
    private $emitter;

    public function __construct(EventEmitter $emitter, SessionManager $manager)
    {
        $this->manager = $manager;
        $this->emitter = $emitter;
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

    public function didOpen(TextDocumentItem $textDocument)
    {
        $this->manager->current()->workspace()->open($textDocument);
        $this->emitter->emit(
            LanguageServerEvents::TEXT_DOCUMENT_OPENED,
            [ $textDocument ]
        );
    }

    public function didChange(VersionedTextDocumentIdentifier $textDocument, array $contentChanges)
    {
        foreach ($contentChanges as $contentChange) {
            $this->manager->current()->workspace()->update(
                $textDocument,
                $contentChange['text']
            );
        }

        $this->emitter->emit(
            LanguageServerEvents::TEXT_DOCUMENT_UPDATED,
            [ $textDocument, $contentChanges ]
        );
    }

    public function didClose(TextDocumentIdentifier $textDocument)
    {
        $this->manager->current()->workspace()->remove(
            $textDocument
        );

        $this->emitter->emit(
            LanguageServerEvents::TEXT_DOCUMENT_CLOSED,
            [ $textDocument ]
        );
    }

    public function didSave(TextDocumentIdentifier $textDocument, string $text = null)
    {
        if ($text !== null) {
            $this->manager->current()->workspace()->get($textDocument->uri)->text = $text;
        }

        $this->emitter->emit(
            LanguageServerEvents::TEXT_DOCUMENT_SAVED,
            [ new DidSaveTextDocumentParams($textDocument, $text) ]
        );
    }

    public function willSave(TextDocumentIdentifier $identifier, int $reason)
    {
        $this->emitter->emit(
            LanguageServerEvents::TEXT_DOCUMENT_WILL_SAVE,
            [
                $identifier,
                $reason
            ]
        );
    }

    public function willSaveWaitUntil(TextDocumentIdentifier $identifier, int $reason)
    {
    }
}
