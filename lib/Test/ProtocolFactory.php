<?php

namespace Phpactor\LanguageServer\Test;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

final class ProtocolFactory
{
    public static function textDocumentItem(string $uri, string $content, int $version = 1): TextDocumentItem
    {
        return new TextDocumentItem($uri, 'php', $version, $content);
    }

    public static function versionedTextDocumentIdentifier(string $uri, int $version): VersionedTextDocumentIdentifier
    {
        return new VersionedTextDocumentIdentifier($version, $uri);
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

    public static function notificationMessage(string $method, array $params): NotificationMessage
    {
        return new NotificationMessage($method, $params);
    }

    public static function range(int $line1, int $char1, int $line2, int $char2): Range
    {
        return new Range(
            self::position($line1, $char1),
            self::position($line2, $char2)
        );
    }

    public static function position(int $line, int $char): Position
    {
        return new Position($line, $char);
    }

    public static function diagnostic(Range $range, string $message): Diagnostic
    {
        return new Diagnostic($range, $message);
    }

    public static function textEdit(int $line1, int $char1, int $line2, int $char2, string $text): TextEdit
    {
        return new TextEdit(self::range($line1, $char1, $line2, $char2), $text);
    }
}
