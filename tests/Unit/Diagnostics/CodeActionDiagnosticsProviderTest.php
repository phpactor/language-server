<?php

namespace Phpactor\LanguageServer\Tests\Unit\Diagnostics;

use Amp\CancellationToken;
use Amp\Delayed;
use Amp\Promise;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Diagnostics\CodeActionDiagnosticsProvider;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;
use function Amp\call;

class CodeActionDiagnosticsProviderTest extends TestCase
{
    public function testProvidesDiagnostics(): void
    {
        $tester = LanguageServerTesterBuilder::create();
        $tester->addDiagnosticsProvider(new CodeActionDiagnosticsProvider(
            new TestCodeActionProvider()
        ));

        $tester = $tester->build();
        $tester->initialize();

        $tester->textDocument()->open('file:///foobar', 'barfoo');

        wait(new Delayed(10));

        self::assertEquals(2, $tester->transmitter()->count());

        $tester->textDocument()->update('file:///foobar', 'bar');
        wait(new Delayed(10));

        self::assertEquals(5, $tester->transmitter()->count());
        self::assertEquals('textDocument/publishDiagnostics', $tester->transmitter()->shiftNotification()->method);
    }
}

class TestCodeActionProvider implements CodeActionProvider
{
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () {
            return [
                CodeAction::fromArray([
                    'title' => 'Bar',
                    'diagnostics' => [
                        ProtocolFactory::diagnostic(
                            ProtocolFactory::range(0, 0, 0, 0),
                            'Foobar'
                        ),
                    ]
                ])
            ];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return ['foo'];
    }

    public function describe(): string
    {
        return 'test';
    }
}
