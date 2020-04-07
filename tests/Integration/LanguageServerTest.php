<?php

namespace Phpactor\LanguageServer\Tests\Integration;

use Amp\CancellationToken;
use Amp\Delayed;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Handler\Example\PingHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\ServerTester;
use Phpactor\TestUtils\PHPUnit\TestCase;

class LanguageServerTest extends TestCase
{
    public function testCancelRequest()
    {
        $tester = LanguageServerBuilder::create()
            ->addSystemHandler(new TestHandler())
            ->buildServerTester();

        $tester->dispatch('longRunner');
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

    public function longRunner(): Promise
    {
        return new Success(false);
    }
}
