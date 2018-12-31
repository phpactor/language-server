<?php

namespace Phpactor\LanguageServer\Core\Session;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Session\Exception\DocumentNotFound;
use Phpactor\LanguageServer\Core\Session\Exception\UnknownDocument;
use RuntimeException;

class Workspace
{
    /**
     * @var TextDocumentItem[]
     */
    private $documents = [];

    private $rootUri;

    private $processId;

    public function __construct(string $rootUri)
    {
        $this->rootUri = $rootUri ?: getcwd();
    }

    public function has(string $uri): bool
    {
        return isset($this->documents[$uri]);
    }

    public function get(string $uri): TextDocumentItem
    {
        if (!isset($this->documents[$uri])) {
            $this->tryAndOpen($uri);
        }

        return $this->documents[$uri];
    }

    public function open(TextDocumentItem $textDocument)
    {
        $this->documents[$textDocument->uri] = $textDocument;
    }

    public function update(VersionedTextDocumentIdentifier $textDocument, $updatedText)
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

    public function initialize(string $rootUri)
    {
        $this->rootUri = $rootUri;
    }

    public function remove(TextDocumentIdentifier $textDocument)
    {
        if (!isset($this->documents[$textDocument->uri])) {
            return;
        }

        unset($this->documents[$textDocument->uri]);
    }

    private function tryAndOpen(string $uri)
    {
        if (!file_exists($uri)) {
            throw new DocumentNotFound(sprintf(
                'Document at "%s" not found',
                $uri
            ));
        }

        $contents = file_get_contents($uri);
        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file at "%s"',
                $uri
            ));
        }
        $this->open(new TextDocumentItem($uri, null, null, $contents));
    }
}
