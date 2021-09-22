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
        $notifier->begin('title');
        $this->assertTokenSentEquals($token);
    }

    public function testProgressInitiatedByServerWithClientCapability(): void
    {
        $factory = $this->createFactoryForClientWithProgressCapability();
        $this->clientWillRespondSuccessToCreateRequest();
        $notifier = $factory->create();

        $this->assertUseProgressNotifications($notifier);
    }

    public function testProgressInitiatedByServerWithClientCapabilityButErrorOccurs(): void
    {
        $factory = $this->createFactoryForClientWithProgressCapability();
        $this->clientWillRespondErrorToCreateRequest();
        $notifier = $factory->create();

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

    private function clientWillRespondSuccessToCreateRequest(): void
    {
        $this->client->responseWatcher()->resolveNextResponse(null);
    }

    private function clientWillRespondErrorToCreateRequest(): void
    {
        $this->client->responseWatcher()->resolveNextResponse(null, new ResponseError(
            ErrorCodes::MethodNotFound,
            'window/workDoneProgress/create',
        ));
    }

    private function assertUseProgressNotifications(ProgressNotifier $notifier): void
    {
        self::assertInstanceOf(WorkDoneProgressNotifier::class, $notifier);
    }

    private function assertTokenSentEquals(WorkDoneToken $token): void
    {
        self::assertCount(1, $this->transmitter);
        self::assertEquals((string) $token, $this->transmitter->shiftNotification()->params['token']);
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
