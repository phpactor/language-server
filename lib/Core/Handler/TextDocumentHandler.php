<?php

namespace Phpactor\LanguageServer\Core\Handler;

use Amp\Emitter;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Event\EventEmitter;
use Phpactor\LanguageServer\Core\Event\LanguageServerEvents;
use Phpactor\LanguageServer\Core\Session\Manager;

final class TextDocumentHandler implements Handler
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var EventEmitter
     */
    private $emitter;

    public function __construct(EventEmitter $emitter, Manager $manager)
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
}
