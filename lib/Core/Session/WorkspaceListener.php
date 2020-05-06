<?php

namespace Phpactor\LanguageServer\Core\Session;

use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\ListenerProviderInterface;

class WorkspaceListener implements ListenerProviderInterface
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof TextDocumentClosed) {
            yield function (TextDocumentClosed $closed) {
                $this->workspace->remove($closed->identifier());
            };
            return;
        }

        if ($event instanceof TextDocumentOpened) {
            yield function (TextDocumentOpened $opened) {
                $this->workspace->open($opened->textDocument());
            };
            return;
        }

        if ($event instanceof TextDocumentUpdated) {
            yield function (TextDocumentUpdated $updated) {
                $this->workspace->update($updated->identifier(), $updated->updatedText());
            };
            return;
        }
    }
}
