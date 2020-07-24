<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

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

    public static function textDocumentIdentifier(string $uri): TextDocumentIdentifier
    {
        return new TextDocumentIdentifier($uri);
    }

    public static function initializeParams(?string $rootUri = null): InitializeParams
    {
        $params = new InitializeParams(new ClientCapabilities());
        $params->rootUri = $rootUri;

        return $params;
    }

    public static function requestMessage(string $method, array $params): RequestMessage
    {
        return new RequestMessage(uniqid(), $method, $params);
    }
}
