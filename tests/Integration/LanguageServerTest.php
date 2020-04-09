<?php

namespace Phpactor\LanguageServer\Tests\Integration;

use Amp\CancellationToken;
use Amp\CancellationTokenSource;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Loop;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\TestUtils\PHPUnit\TestCase;

class LanguageServerTest extends TestCase
{
    public function testCancelRequest()
    {
        $this->expectException(CancelledException::class);

        $tester = LanguageServerBuilder::create()
            ->addSystemHandler(new TestHandler())
        ->catchExceptions(false)
            ->buildServerTester();

        $source = new CancellationTokenSource();
        $token = $source->getToken();

        \Amp\asyncCall(function () use ($tester, $token) {
            yield $tester->dispatch(1, 'longRunner', [], [
                $token
            ]);
        });
        \Amp\asyncCall(function () use ($tester, $source) {
            yield new Delayed(20);
            $source->cancel();
        });

        Loop::run();
    }
}

class TestHandler implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'longRunner' => 'longRunner',
        ];
    }

    public function longRunner(CancellationToken $token): Promise
    {
        return \Amp\call(function (CancellationToken $token) {
            while (true) {
                $token->throwIfRequested();
                yield new Delayed(10);
            }
        }, $token);
    }
}
