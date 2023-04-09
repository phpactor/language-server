<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument;

use Amp\Promise;
use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\DocumentFormattingParams;
use Phpactor\LanguageServerProtocol\DocumentFormattingRequest;
use Phpactor\LanguageServerProtocol\FormattingOptions;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use Phpactor\LanguageServer\Handler\TextDocument\FormattingHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;

class FormattingHandlerTest extends TestCase
{
    public function testProvidesFormatting(): void
    {
        $tester = LanguageServerTesterBuilder::create();
        $tester->addHandler(new FormattingHandler(
            $tester->workspace(),
            new TestFormatter(
                ProtocolFactory::textEdit(0, 0, 0, 0, 'Hello'),
            ),
        ));

        $tester = $tester->build();

        $tester->textDocument()->open('file://foobar', 'barfoo');

        $response = $tester->requestAndWait(DocumentFormattingRequest::METHOD, new DocumentFormattingParams(
            ProtocolFactory::textDocumentIdentifier('file://foobar'),
            new FormattingOptions(4, false),
        ));

        self::assertCount(1, $response->result, 'Example formatter provided results');
    }
}

class TestFormatter implements Formatter
{
    private TextEdit $textEdit;

    public function __construct(TextEdit $textEdit)
    {
        $this->textEdit = $textEdit;
    }

    public function format(TextDocumentItem $textDocument): Promise
    {
        return new Success([$this->textEdit]);
    }
}
