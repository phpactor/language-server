<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Diagnostics;

use Amp\CancellationTokenSource;
use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\ClosureDiagnosticsProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\call;
use function Amp\delay;

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
    }

    /**
     * @return Generator<mixed>
     */
    public function testPublishesForManyFiles(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 0, 0);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///barfoo', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///bazbar', 'foobar'));

        yield new Delayed(0);

        $token->cancel();

        self::assertEquals(3, $tester->transmitter()->count());
    }

    /**
     * @return Generator<mixed>
     */
    public function testDeduplicatesSuccessiveChangesToSameFile(): Generator
    {
        $tester = LanguageServerTesterBuilder::create();
        $engine = $this->createEngine($tester, 5, 0);

        $token = new CancellationTokenSource();
        $promise = $engine->run($token->getToken());

        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'bazboo'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));
        $engine->enqueue(ProtocolFactory::textDocumentItem('file:///foobar', 'foobar'));

        yield new Delayed(10);

        $token->cancel();

        self::assertEquals(1, $tester->transmitter()->count());
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

    private function createEngine(LanguageServerTesterBuilder $tester, int $delay = 0, int $sleepTime = 0): DiagnosticsEngine
    {
        $engine = new DiagnosticsEngine($tester->clientApi(), new ClosureDiagnosticsProvider(function (TextDocumentItem $item) use ($delay) {
            return call(function () use ($delay) {
                if ($delay) {
                    yield delay($delay);
                }
                return [
                    ProtocolFactory::diagnostic(
                        ProtocolFactory::range(0, 0, 0, 0),
                        'Foobar is broken'
                    )
                ];
            });
        }), $sleepTime);
        return $engine;
    }
}
