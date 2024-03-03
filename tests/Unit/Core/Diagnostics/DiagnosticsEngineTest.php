<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Diagnostics;

use Amp\CancellationTokenSource;
use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Generator;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\ClosureDiagnosticsProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\delay;
use Exception;

class DiagnosticsEngineTest extends AsyncTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function testPublishesDiagnostics(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 0, 0);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(
            ProtocolFactory::textDocumentItem('file:///foobar', 'foobar')
        );

        yield new Delayed(10);

        $token->cancel();

        $notification = $tester->transmitter()->shiftNotification();
        self::assertNotNull($notification, 'Notification sent');
        self::assertEquals('textDocument/publishDiagnostics', $notification->method);
        self::assertEquals([], $notification->params['diagnostics'] ?? null);

        $notification = $tester->transmitter()->shiftNotification();
        self::assertNotNull($notification, 'Notification sent');
        self::assertEquals('textDocument/publishDiagnostics', $notification->method);
        /** @phpstan-ignore-next-line */
        self::assertEquals('Foobar is broken', $notification->params['diagnostics'][0]->message ?? null);
    }

    /**
     * @return Generator<mixed>
     */
    public function testOnlyPublishesForMostRecentFile(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 0, 0);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///barfoo', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///bazbar', 'foobar'));

        yield new Delayed(1);

        $token->cancel();

        // includes reset diagnostic
        self::assertEquals(2, $tester->transmitter()->count());
    }

    /**
     * @return Generator<mixed>
     */
    public function testDoesNotProcessMoreThanOneDocument(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 1, 0);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));

        yield new Delayed(10);

        $token->cancel();

        // clear + publish
        self::assertEquals(2, $tester->transmitter()->count());
    }

    /**
     * @return Generator<mixed>
     */
    public function testSleepPreventsSeige(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 5, 10);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'bazboo'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///barfoo', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///bazbar', 'foobar'));

        yield new Delayed(100);

        $token->cancel();

        self::assertEquals(2, $tester->transmitter()->count());
    }

    public function testAggregatesResultsFromMultipleProviders(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = new DiagnosticsEngine($tester->clientApi(), new NullLogger(), [
            new ClosureDiagnosticsProvider(function (TextDocumentItem $item) {
                return new Success([
                    ProtocolFactory::diagnostic(ProtocolFactory::range(0, 0, 0, 0), 'Foobar is broken')
                ]);
            }),
            new ClosureDiagnosticsProvider(function (TextDocumentItem $item) {
                return new Success([
                    ProtocolFactory::diagnostic(ProtocolFactory::range(0, 0, 0, 0), 'Barfoo is broken')
                ]);
            })
        ], 10);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'bazboo'));

        yield new Delayed(100);

        self::assertEquals(3, $tester->transmitter()->count());

    }

    public function testHandlesLinterExceptions(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = new DiagnosticsEngine($tester->clientApi(), new NullLogger(), [
            new ClosureDiagnosticsProvider(function (TextDocumentItem $item): void {
                throw new Exception('oh dear');
            }),
        ], 10);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'bazboo'));

        yield new Delayed(100);

        self::assertEquals(2, $tester->transmitter()->count());
        $notification = $tester->transmitter()->shiftNotification();
        $notification = $tester->transmitter()->shiftNotification();
        assert($notification instanceof NotificationMessage);
        self::assertEquals('window/showMessage', $notification->method);
        self::assertStringContainsString('oh dear', ((string)($notification->params['message'] ?? '')));

    }

    /**
     * Note that this test was added in relation to the race condition in
     * https://github.com/phpactor/phpactor/issues/1974
     *
     * It DOES NOT reproduce the race condition sadly.
     *
     * See the commit this change was introduced to see what it SHOULD have covered.
     *
     * @return Generator<mixed>
     */
    public function testAlwaysAnalyzesTheLastChangeLast(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $lastDocument = '';
        $engine = $this->createEngine($tester, 5, 0, $lastDocument);

        $token = new CancellationTokenSource();
        asyncCall(function () use ($engine, $token) {
            yield $engine->run($token->getToken());
        });
        yield new Delayed(1);

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', '1', version: 1));
        yield new Delayed(1);
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', '2', version: 2));
        yield new Delayed(10);
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', '3', version: 3));
        yield new Delayed(1);
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', '4', version: 4));
        yield new Delayed(1);
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', '5', version: 5));

        yield new Delayed(100);

        $token->cancel();

        self::assertEquals('5', $lastDocument);
    }

    private function createEngine(LanguageServerTesterBuilder $tester, int $delay = 0, int $sleepTime = 0, string &$lastDocument = null): DiagnosticsEngine
    {
        return new DiagnosticsEngine($tester->clientApi(), new NullLogger(), [
            new ClosureDiagnosticsProvider(function (TextDocumentItem $item) use ($delay, &$lastDocument) {
                return call(function () use ($delay, $item, &$lastDocument) {
                    if ($delay) {
                        yield delay($delay);
                    }
                    $lastDocument = $item->text;
                    return [
                        ProtocolFactory::diagnostic(ProtocolFactory::range(0, 0, 0, 0), 'Foobar is broken')
                    ];
                });
            })
        ], $sleepTime);
    }
}
