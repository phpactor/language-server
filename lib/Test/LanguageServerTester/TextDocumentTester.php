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

class TextDocumentTester
{
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
        $this->tester->notifyAndWait(DidOpenTextDocumentNotification::METHOD, new DidOpenTextDocumentParams(
            ProtocolFactory::textDocumentItem($url, $content)
        ));
    }

    public function update(string $uri, string $newText): void
    {
        $this->tester->notifyAndWait(DidChangeTextDocumentNotification::METHOD, new DidChangeTextDocumentParams(
            ProtocolFactory::versionedTextDocumentIdentifier($uri, 1),
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
