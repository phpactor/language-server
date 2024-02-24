<?php

namespace Phpactor\LanguageServer\Test\LanguageServerTester;

use Phpactor\LanguageServerProtocol\DidChangeTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidChangeTextDocumentParams;
use Phpactor\LanguageServerProtocol\DidSaveTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidSaveTextDocumentParams;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentParams;
use Phpactor\LanguageServerProtocol\DidOpenTextDocumentNotification;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use RuntimeException;

class TextDocumentTester
{
    /**
     * @var array<string,int>
     */
    private static $versions = [];

    /**
     * @var LanguageServerTester
     */
    private $tester;

    public function __construct(LanguageServerTester $tester)
    {
        $this->tester = $tester;
    }

    public function open(string $url, string $content): void
    {
        self::$versions[$url] = 1;
        $this->tester->notifyAndWait(DidOpenTextDocumentNotification::METHOD, new DidOpenTextDocumentParams(
            ProtocolFactory::textDocumentItem($url, $content)
        ));
    }

    public function update(string $uri, string $newText): void
    {
        if (!isset(self::$versions[$uri])) {
            throw new RuntimeException(sprintf(
                'Cannot update document that has not been opened: %s',
                $uri
            ));
        }
        self::$versions[$uri]++;
        $this->tester->notifyAndWait(DidChangeTextDocumentNotification::METHOD, new DidChangeTextDocumentParams(
            ProtocolFactory::versionedTextDocumentIdentifier($uri, self::$versions[$uri]),
            [
                [
                    'text' => $newText
                ]
            ]
        ));
    }

    public function save(string $uri): void
    {
        $this->tester->notifyAndWait(DidSaveTextDocumentNotification::METHOD, new DidSaveTextDocumentParams(
            ProtocolFactory::versionedTextDocumentIdentifier($uri, 1)
        ));
    }
}
