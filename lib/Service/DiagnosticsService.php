<?php

namespace Phpactor\LanguageServer\Service;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\ListenerProviderInterface;

class DiagnosticsService implements ServiceProvider, ListenerProviderInterface
{
    /**
     * @var DiagnosticsEngine
     */
    private $engine;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var bool
     */
    private $lintOnUpdate;

    /**
     * @var bool
     */
    private $lintOnSave;

    public function __construct(
        DiagnosticsEngine $engine,
        bool $lintOnUpdate = true,
        bool $lintOnSave = true,
        ?Workspace $workspace = null
    ) {
        $this->engine = $engine;
        $this->workspace = $workspace ?: new Workspace();
        $this->lintOnUpdate = $lintOnUpdate;
        $this->lintOnSave = $lintOnSave;
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
        if ($this->lintOnUpdate && $event instanceof TextDocumentUpdated) {
            yield [$this, 'enqueueUpdate'];
        }

        if ($this->lintOnSave && $event instanceof TextDocumentSaved) {
            yield [$this, 'enqueueSave'];
        }
    }

    public function enqueueUpdate(TextDocumentUpdated $update): void
    {
        $item = new TextDocumentItem(
            $update->identifier()->uri,
            'php',
            $update->identifier()->version,
            $update->updatedText()
        );

        $this->engine->enqueue($item);
    }

    public function enqueueSave(TextDocumentSaved $save): void
    {
        $item = new TextDocumentItem(
            $save->identifier()->uri,
            'php',
            $save->identifier()->version ?? 1, // VIM lsp client seems delivers NULL here, so just use an arbitrary identifier
            $save->text() ?: $this->workspace->get($save->identifier()->uri)->text
        );

        $this->engine->enqueue($item);
    }
}
