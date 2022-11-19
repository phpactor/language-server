<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\WorkDoneProgress\SilentWorkDoneProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use Phpactor\TestUtils\PHPUnit\TestCase;
use function Amp\Promise\wait;

class SilentProgressNotifierTest extends TestCase
{
    public function testNotifier(): void
    {
        $notifier = new SilentWorkDoneProgressNotifier();
        $token = WorkDoneToken::generate();
        $response = wait($notifier->create($token));
        self::assertInstanceOf(ResponseMessage::class, $response);
        $notifier->begin($token, 'Foobar');
        $notifier->report($token);
        $notifier->end($token);
    }
}
