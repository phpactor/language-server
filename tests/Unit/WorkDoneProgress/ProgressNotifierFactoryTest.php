<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use Amp\PHPUnit\AsyncTestCase;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\Rpc\ErrorCodes;
use Phpactor\LanguageServer\Core\Rpc\ResponseError;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\MessageProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifierFactory;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;

final class ProgressNotifierFactoryTest extends AsyncTestCase
{
    /**
     * @var TestRpcClient
     */
    private $client;

    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = TestRpcClient::create();
        $this->transmitter = $this->client->transmitter();
    }

    public function testProgressInitiatedByClient(): void
    {
        $factory = $this->createFactoryForClientWithoutProgressCapability();
        $token = WorkDoneToken::generate();
        $notifier = $factory->create($token);

        $this->assertUseProgressNotifications($notifier);
        $this->assertNotifyUsingToken($notifier, $token);
    }

    public function testProgressInitiatedByServerWithClientCapability(): void
    {
        $factory = $this->createFactoryForClientWithProgressCapability();
        $notifier = $factory->create();
        $this->respondWithSuccess();
        $token = $this->grabTokenFromCreateRequest();

        $this->assertUseProgressNotifications($notifier);
        $this->assertNotifyUsingToken($notifier, $token);
    }

    public function testProgressInitiatedByServerWithClientCapabilityButErrorOccurs(): void
    {
        $factory = $this->createFactoryForClientWithProgressCapability();
        $notifier = $factory->create();
        $this->respondWithError();

        $this->assertUseMessageNotifications($notifier);
    }

    public function testProgressInitiatedByServerWithoutClientCapability(): void
    {
        $factory = $this->createFactoryForClientWithoutProgressCapability();
        $notifier = $factory->create();

        $this->assertUseMessageNotifications($notifier);
    }

    private function createFactoryForClientWithoutProgressCapability(): ProgressNotifierFactory
    {
        return $this->createFactory(false);
    }

    private function createFactoryForClientWithProgressCapability(): ProgressNotifierFactory
    {
        return $this->createFactory(true);
    }

    private function createFactory(bool $WorkDoneProgress) : ProgressNotifierFactory
    {
        $api = new ClientApi($this->client);
        $capabilities = ClientCapabilities::fromArray([
            'window' => ['workDoneProgress' => $WorkDoneProgress],
        ]);

        return new ProgressNotifierFactory($api, $capabilities);
    }

    private function assertNotifyUsingToken(ProgressNotifier $notifier, string $token): void
    {
        $this->transmitter->clear();
        $notifier->begin('title');
        self::assertCount(1, $this->transmitter);
        self::assertEquals(
            $token,
            $this->transmitter->shiftNotification()->params['token'],
            'The token does not match',
        );
    }

    private function grabTokenFromCreateRequest(): string
    {
        self::assertCount(1, $this->transmitter, 'The "create" request was not send');

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

    private function respondWithSuccess(): void
    {
        $this->client->responseWatcher()->resolveLastResponse(null);
    }

    private function respondWithError(): void
    {
        $this->client->responseWatcher()->resolveLastResponse(null, new ResponseError(
            ErrorCodes::MethodNotFound,
            'window/workDoneProgress/create',
        ));
    }

    private function assertUseProgressNotifications(ProgressNotifier $notifier): void
    {
        self::assertInstanceOf(WorkDoneProgressNotifier::class, $notifier);
    }

    private function assertUseMessageNotifications(ProgressNotifier $notifier): void
    {
        if ($notifier instanceof MessageProgressNotifier) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->transmitter->clear();
        $notifier->begin('title');
        self::assertCount(1, $this->transmitter, 'Does not use message notification');
        self::assertEquals(
            'window/showMessage',
            $this->transmitter->shiftNotification()->method,
            'Does not use message notification'
        );
    }
}
