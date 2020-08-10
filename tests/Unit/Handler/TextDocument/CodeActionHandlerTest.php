<?php

namespace Phpactor\LanguageServer\Tests\Unit\Handler\TextDocument;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\CodeActionContext;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServer\Core\CodeAction\AggregateCodeActionProvider;
use Phpactor\LanguageServer\Example\CodeAction\SayHelloCodeActionProvider;
use Phpactor\LanguageServer\Handler\TextDocument\CodeActionHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;

class CodeActionHandlerTest extends TestCase
{
    public function testProvidesCodeActions(): void
    {
        $tester = LanguageServerTesterBuilder::create();
        $tester->addHandler(new CodeActionHandler(
            new AggregateCodeActionProvider(
                new SayHelloCodeActionProvider()
            ),
            $tester->workspace()
        ));
        $tester = $tester->build();

        $tester->textDocument()->open('file://foobar', 'barfoo');

        $response = $tester->requestAndWait(CodeActionRequest::METHOD, new CodeActionParams(
            ProtocolFactory::textDocumentIdentifier('file://foobar'),
            ProtocolFactory::range(0, 0, 0, 0),
            new CodeActionContext([])
        ));

        self::assertCount(2, $response->result, 'Example provider provided results');
    }
}
