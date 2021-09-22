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

    public function testProgressInitiatedByClient(): void
    {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifier($token);

        $this->assertNotifyUsingToken($notifier, $token);
    }

    public function testProgressInitiatedByServerAndAccepted(): void
    {
        $notifier = $this->createNotifier();
        $this->respondWithSuccess();

        $token = $this->grabTokenFromCreateRequest();

        $this->assertNotifyUsingToken($notifier, $token);
    }

    public function testProgressInitiatedByServerButRefused(): void
    {
        $notifier = $this->createNotifier();
        $this->respondWithError();

        $this->assertUseMessageNotifications($notifier);
    }

    public function testDoesNotSendAnythingAfterEndNotification(): void
    {
        $notifier = $this->createNotifier(WorkDoneToken::generate());
        $notifier->begin('title');
        $notifier->end();
        $notifier->report();

        $this->assertNumberOfSentNotifications(2, 'No progress should be reported after calling end()');
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

    private function assertNotifyUsingToken(WorkDoneProgressNotifier $notifier, string $token): void
    {
        $this->transmitter->clear();
        $notifier->begin('title');
        $this->assertNumberOfSentNotifications(1);
        self::assertEquals(
            $token,
            $this->transmitter->shiftNotification()->params['token'],
            'The token does not match',
        );
    }

    private function respondWithSuccess(): void
    {
        $this->api->responseWatcher()->resolveLastResponse(null);
    }

    private function respondWithError(): void
    {
        $this->api->responseWatcher()->resolveLastResponse(null, new ResponseError(
            ErrorCodes::MethodNotFound,
            'window/workDoneProgress/create',
        ));
    }

    private function grabTokenFromCreateRequest(): string
    {
        $this->assertNumberOfSentNotifications(1, 'The "create" request was not send');

        $createMessage = $this->transmitter->shiftRequest();
        $token = $createMessage->params['token'];

        self::assertEquals(
            'window/workDoneProgress/create',
            $createMessage->method,
            'The "create" request was not send',
        );
        self::assertNotEmpty($token, 'The token is missing from the "create" request');

        return $token;
    }

    private function assertUseMessageNotifications(WorkDoneProgressNotifier $notifier): void
    {
        $this->transmitter->clear();
        $notifier->begin('title');
        self::assertCount(1, $this->transmitter);
        self::assertEquals('window/showMessage', $this->transmitter->shiftNotification()->method);
    }

    private function assertNumberOfSentNotifications(int $count, ?string $message = null): void
    {
        self::assertCount(
            $count,
            $this->transmitter,
            $message ?: "$count notifications should have been sent",
        );
    }

    private function assertValidProgressNotification(string $token, array $values): void
    {
        $this->assertNumberOfSentNotifications(1);
        $notification = $this->transmitter->shiftNotification();
        self::assertEquals('$/progress', $notification->method);
        self::assertEquals($token, $notification->params['token']);
        self::assertEquals($values, $notification->params['value']);
    }
}
