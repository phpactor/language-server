<?php

namespace Phpactor\LanguageServer\Core\Session;

use Countable;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentIdentifier;

interface Workspace extends Countable
{
    public function has(string $uri): bool;

    public function get(string $uri): TextDocumentItem;

    public function open(TextDocumentItem $textDocument): void;

    public function update(VersionedTextDocumentIdentifier $textDocument, string $updatedText): void;

    public function openFiles(): int;

    public function remove(TextDocumentIdentifier $textDocument): void;

    public function count(): int;
}
