<?php

namespace Phpactor\LanguageServer\Core\Session;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Session\Exception\UnknownDocument;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @implements IteratorAggregate<string,TextDocumentItem>
 */
class Workspace implements Countable, IteratorAggregate
{
    /**
     * @var TextDocumentItem[]
     */
    private $documents = [];

    /**
     * @var int
     */
    private $processId;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatchter;

    public function __construct(EventDispatcherInterface $dispatchter = null)
    {
        $this->dispatchter = $dispatchter ?: new NullEventDispatcher();
    }

    public function has(string $uri): bool
    {
        return isset($this->documents[$uri]);
    }

    public function get(string $uri): TextDocumentItem
    {
        if (!isset($this->documents[$uri])) {
            throw new UnknownDocument($uri);
        }

        return $this->documents[$uri];
    }

    public function open(TextDocumentItem $textDocument): void
    {
        $this->documents[$textDocument->uri] = $textDocument;
        $this->dispatchter->dispatch(new TextDocumentOpened($textDocument));
    }

    public function update(VersionedTextDocumentIdentifier $textDocument, string $updatedText): void
    {
        if (!isset($this->documents[$textDocument->uri])) {
            throw new UnknownDocument($textDocument->uri);
        }

        $this->documents[$textDocument->uri]->text = $updatedText;
        $this->dispatchter->dispatch(new TextDocumentUpdated($textDocument, $updatedText));
    }

    public function openFiles(): int
    {
        return count($this->documents);
    }

    public function remove(TextDocumentIdentifier $identifier): void
    {
        if (!isset($this->documents[$identifier->uri])) {
            return;
        }

        unset($this->documents[$identifier->uri]);
        $this->dispatchter->dispatch(new TextDocumentClosed($identifier));
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->openFiles();
    }

    /**
     * @return ArrayIterator<string,TextDocumentItem>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->documents);
    }
}
