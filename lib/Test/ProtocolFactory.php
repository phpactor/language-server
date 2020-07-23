<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;

final class ProtocolFactory
{
    public static function textDocumentItem(string $uri, string $content): TextDocumentItem
    {
        return new TextDocumentItem($uri, 'php', 1, $content);
    }

    public static function versionedTextDocumentIdentifier(string $string, ?int $version = null): VersionedTextDocumentIdentifier
    {
        return new VersionedTextDocumentIdentifier('foobar');
    }
}
