<?php

namespace Phpactor\LanguageServer\Core\Session;

use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Session\Exception\UnknownDocument;

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

    public function get(string $uri): TextDocumentItem
    {
        if (!isset($this->documents[$uri])) {
            throw new UnknownDocument($uri);
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
}
