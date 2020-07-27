<?php

namespace Phpactor\LanguageServer\Test\LanguageServerTester;

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
}
