<?php

namespace Phpactor\LanguageServer\Service;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\ListenerProviderInterface;

class DiagnosticsService implements ServiceProvider, ListenerProviderInterface
{
    public function __construct(
        private DiagnosticsEngine $engine,
        private bool $lintOnUpdate = true,
        private bool $lintOnSave = true,
        private Workspace $workspace = new Workspace(),
        private bool $clearOnUpdate = true,
        private bool $lintOnOpen = true
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        return [
            'diagnostics',
        ];
    }

    /**
     * @return Promise<bool>
     */
    public function diagnostics(CancellationToken $cancellationToken): Promise
    {
        return $this->engine->run($cancellationToken);
    }

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($this->lintOnOpen && $event instanceof TextDocumentOpened) {
            yield [$this, 'opened'];
        }

        if ($this->lintOnUpdate && $event instanceof TextDocumentUpdated) {
            yield [$this, 'enqueueUpdate'];
        }

        if ($this->lintOnSave && $event instanceof TextDocumentSaved) {
            yield [$this, 'enqueueSave'];
        }
    }

    public function opened(TextDocumentOpened $opened): void
    {
        $this->engine->enqueue($opened->textDocument());
    }

    public function enqueueUpdate(TextDocumentUpdated $update): void
    {
        $item = new TextDocumentItem(
            $update->identifier()->uri,
            'php',
            $update->identifier()->version,
            $update->updatedText()
        );

        if ($this->clearOnUpdate) {
            $this->engine->clear($item);
        }

        $this->engine->enqueue($item);
    }

    public function enqueueSave(TextDocumentSaved $save): void
    {
        $version = $this->workspace->get($save->identifier()->uri)->version;

        $item = new TextDocumentItem(
            $save->identifier()->uri,
            'php',
            $version,
            $save->text() ?: $this->workspace->get($save->identifier()->uri)->text
        );

        $this->engine->enqueue($item);
    }
}
