<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use RuntimeException;

class WorkDoneProgressNotifierTest extends TestCase
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

    public function testCreateInitiatedByClient(): void
    {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifier($token);

        $notifier->begin('title');
        $this->assertNotifiedWithToken($token);
    }

    public function testCreateInitiatedByServerAccepted(): void
    {
        $this->clientWillRespondSuccessToCreateRequest();
        $notifier = $this->createNotifier();

        $this->assertNumberOfSentNotifications(1);
        $createMessage = $this->transmitter->shiftRequest();
        $token = $createMessage->params['token'];
        self::assertEquals('window/workDoneProgress/create', $createMessage->method);
        self::assertNotEmpty($token);

        $notifier->begin('title');
        $this->assertNotifiedWithToken($token);
    }

    public function testCreateInitiatedByServerRefused(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('window/workDoneProgress/create');
        self::expectExceptionCode(ErrorCodes::MethodNotFound);

        $this->clientWillRespondErrorToCreateRequest();
        $notifier = $this->createNotifier();
    }

    public function testDoesNotSendAnythingAfterEndNotification(): void
    {
        $notifier = $this->createNotifier(WorkDoneToken::generate());
        $notifier->begin('title');
        $notifier->end();
        $this->assertNumberOfSentNotifications(2);
        $this->transmitter->clear();

        $notifier->report();
        $this->assertNumberOfSentNotifications(0, 'No progress should be reported after calling end()');
    }

    /**
     * @dataProvider provideNotificationArguments
     */
    public function testSendCorrectNotifications(
        string $title,
        ?string $message = null,
        ?int $percentage = null
    ): void {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifier($token);

        $notifier->begin($title, $message, $percentage);
        $this->assertValidProgressNotification($token, [
            'kind' => 'begin',
            'title' => $title,
            'message' => $message,
            'percentage' => $percentage,
            'cancellable' => null,
        ]);

        $notifier->report($message, $percentage);
        $this->assertValidProgressNotification($token, [
            'kind' => 'report',
            'message' => $message,
            'percentage' => $percentage,
            'cancellable' => null,
        ]);

        $notifier->end($message);
        $this->assertValidProgressNotification($token, [
            'kind' => 'end',
            'message' => $message,
        ]);
    }

    public function provideNotificationArguments(): iterable
    {
        yield 'Title only' => ['Title'];

        yield 'Title and message' => ['Title', 'Message'];

        yield 'Title and percentage' => ['Title', null, 35];

        yield 'Title, message and percentage' => ['Title', 'Message', 35];
    }

    private function createNotifier(?WorkDoneToken $token = null): WorkDoneProgressNotifier
    {
        return new WorkDoneProgressNotifier(new ClientApi($this->api), $token);
    }

    private function clientWillRespondSuccessToCreateRequest(): void
    {
        $this->api->responseWatcher()->resolveNextResponse(null);
    }

    private function clientWillRespondErrorToCreateRequest(): void
    {
        $this->api->responseWatcher()->resolveNextResponse(null, new ResponseError(
            ErrorCodes::MethodNotFound,
            'window/workDoneProgress/create',
        ));
    }

    private function assertValidProgressNotification(string $token, array $values): void
    {
        $this->assertNumberOfSentNotifications(1);
        $notification = $this->transmitter->shiftNotification();
        self::assertEquals('$/progress', $notification->method);
        self::assertEquals($token, $notification->params['token']);
        self::assertEquals($values, $notification->params['value']);
    }

    private function assertNumberOfSentNotifications(int $count, ?string $message = null): void
    {
        self::assertCount(
            $count,
            $this->transmitter,
            $message ?: "$count notifications should have been sent",
        );
    }

    private function assertNotifiedWithToken(string $token): void
    {
        $this->assertNumberOfSentNotifications(1);
        self::assertEquals(
            $token,
            $this->transmitter->shiftNotification()->params['token'],
            'The token does not match',
        );
    }
}
