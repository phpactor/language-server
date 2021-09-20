<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\MessageProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;

class MessageProgressNotifierTest extends TestCase
{
    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;
    /**
     * @var TestRpcClient
     */
    private $api;

    protected function setUp(): void
    {
        $this->transmitter = new TestMessageTransmitter();
        $this->api = new TestRpcClient($this->transmitter, new TestResponseWatcher());
    }

    /**
     * @dataProvider provideMessageArguments
     */
    public function testReport(
        string $expectedBeginMessage,
        string $expectedReportMessage,
        string $expectedEndMessage,
        string $title,
        ?string $message = null,
        ?int $percentage = null
    ): void {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifier();

        $notifier->begin($token, $title, $message, $percentage);
        self::assertEquals(1, $this->transmitter->count());
        $sentMessage = $this->transmitter->shiftNotification()->params['message'];
        self::assertEquals($expectedBeginMessage, $sentMessage, 'Error in begin message');

        $notifier->report($token, $message, $percentage);
        self::assertEquals(1, $this->transmitter->count());
        $sentMessage = $this->transmitter->shiftNotification()->params['message'];
        self::assertEquals($expectedReportMessage, $sentMessage, 'Error in report message');

        $notifier->end($token, $message);
        self::assertEquals(1, $this->transmitter->count());
        $sentMessage = $this->transmitter->shiftNotification()->params['message'];
        self::assertEquals($expectedEndMessage, $sentMessage, 'Error in end message');
    }

    public function provideMessageArguments(): iterable
    {
        yield 'Title only' => [
            'Title',
            'Title',
            'Title',
            'Title',
        ];

        yield 'Title and message' => [
            'Title: Message',
            'Title: Message',
            'Title: Message',
            'Title',
            'Message',
        ];

        yield 'Title and percentage' => [
            'Title: 35% done',
            'Title: 35% done',
            'Title',
            'Title',
            null,
            35,
        ];

        yield 'Title, message and percentage' => [
            'Title: Message, 35% done',
            'Title: Message, 35% done',
            'Title: Message',
            'Title',
            'Message',
            35,
        ];
    }

    private function createNotifier(): ProgressNotifier
    {
        return new MessageProgressNotifier(new ClientApi($this->api));
    }
}
