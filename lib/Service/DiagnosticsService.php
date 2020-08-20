<?php

namespace Phpactor\LanguageServer\Service;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\ListenerProviderInterface;

class DiagnosticsService implements ServiceProvider, ListenerProviderInterface
{
    /**
     * @var DiagnosticsEngine
     */
    private $engine;

    public function __construct(DiagnosticsEngine $engine)
    {
        $this->engine = $engine;
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
        if ($event instanceof TextDocumentUpdated) {
            yield [$this, 'enqueueUpdate'];
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
}
