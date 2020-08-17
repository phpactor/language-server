<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
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

    public static function versionedTextDocumentIdentifier(?string $uri = 'foobar', ?int $version = null): VersionedTextDocumentIdentifier
    {
        return new VersionedTextDocumentIdentifier($uri, $version);
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

    public static function range(int $line1, int $col1, int $line2, int $col2): Range
    {
        return new Range(
            self::position($line1, $col1),
            self::position($line2, $col2)
        );
    }

    public static function position(int $lineNb, int $colNb): Position
    {
        return new Position($lineNb, $colNb);
    }

    public static function diagnostic(Range $range, string $message): Diagnostic
    {
        return new Diagnostic($range, $message);
    }
}
