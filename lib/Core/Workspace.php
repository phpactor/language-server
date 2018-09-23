<?php

namespace Phpactor\LanguageServer\Core;

use LanguageServerProtocol\TextDocumentItem;

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

    public function update(TextDocumentItem $textDocument, $updatedText)
    {
        if (!isset($this->documents[$textDocument->uri])) {
            throw new UnknownDocument($textDocument->uri);
        }

        $this->documents[$textDocument->uri]->text = $updatedText;
    }

    public function initialize(string $rootUri)
    {
        $this->rootUri = $rootUri;
    }
}
