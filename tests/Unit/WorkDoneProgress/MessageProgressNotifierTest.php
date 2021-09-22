<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\MessageProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;

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
     * @dataProvider provideNotificationArguments
     */
    public function testSendCorrectNotifications(
        string $expectedBeginMessage,
        string $expectedReportMessage,
        string $expectedEndMessage,
        string $title,
        ?string $message = null,
        ?int $percentage = null
    ): void {
        $notifier = $this->createNotifier();

        $notifier->begin($title, $message, $percentage);
        $this->assertMessageEquals($expectedBeginMessage, 'Error in begin message');

        $notifier->report($message, $percentage);
        $this->assertMessageEquals($expectedReportMessage, 'Error in report message');

        $notifier->end($message);
        $this->assertMessageEquals($expectedEndMessage, 'Error in end message');
    }

    public function provideNotificationArguments(): iterable
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

    private function assertMessageEquals(
        string $expectedBeginMessage,
        string $errorMessage
    ): void {
        self::assertEquals(1, $this->transmitter->count());
        $sentMessage = $this->transmitter->shiftNotification()->params['message'];
        self::assertEquals($expectedBeginMessage, $sentMessage, $errorMessage);
    }
}
