<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Handler\TextDocument;

use LanguageServerProtocol\TextDocumentContentChangeEvent;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Handler\TextDocument\DidOpen;
use Phpactor\LanguageServer\Tests\Unit\Core\Handler\HandlerTestCase;

class DidChangeTest extends HandlerTestCase
{
    public function handler(): Handler
    {
        return new DidChange($this->session());
    }

    public function testUpdatesDocument()
    {
    }
}
