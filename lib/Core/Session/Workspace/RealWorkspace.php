<?php

namespace Phpactor\LanguageServer\Core\Session\Workspace;

use Countable;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Session\Exception\UnknownDocument;
use Phpactor\LanguageServer\Core\Session\Workspace;

class RealWorkspace implements Workspace
{
    /**
     * @var TextDocumentItem[]
     */
    private $documents = [];

    private $processId;

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
    }

    public function update(VersionedTextDocumentIdentifier $textDocument, $updatedText): void
    {
        if (!isset($this->documents[$textDocument->uri])) {
            throw new UnknownDocument($textDocument->uri);
        }

        $this->documents[$textDocument->uri]->text = $updatedText;
    }

    public function openFiles(): int
    {
        return count($this->documents);
    }

    public function remove(TextDocumentIdentifier $textDocument): void
    {
        if (!isset($this->documents[$textDocument->uri])) {
            return;
        }

        unset($this->documents[$textDocument->uri]);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->openFiles();
    }
}
