<?php

namespace Phpactor\LanguageServer\Tests\Unit\WorkDoneProgress;

use Amp\PHPUnit\AsyncTestCase;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\WorkDoneProgress\ClientCapabilityDependentProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;

final class ClientCapabilityDependentProgressNotifierTest extends AsyncTestCase
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

    public function testNotifyWithWorkDoneProgressCapability(): void
    {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifierWithProgressCapability();

        $notifier->create($token);
        $message = $this->transmitter->shiftRequest();
        self::assertEquals('window/workDoneProgress/create', $message->method);
        self::assertEquals((string) $token, $message->params['token']);

        $notifier->begin($token, 'title', 'begin message');
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('$/progress', $message->method);
        self::assertEquals((string) $token, $message->params['token']);
        self::assertEquals('begin', $message->params['value']['kind']);
        self::assertEquals('title', $message->params['value']['title']);
        self::assertEquals('begin message', $message->params['value']['message']);
        self::assertNull($message->params['value']['percentage']);
        self::assertNull($message->params['value']['cancellable']);

        $notifier->report($token, 'report message', 30);
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('$/progress', $message->method);
        self::assertEquals((string) $token, $message->params['token']);
        self::assertEquals('report', $message->params['value']['kind']);
        self::assertEquals('report message', $message->params['value']['message']);
        self::assertEquals(30, $message->params['value']['percentage']);
        self::assertNull($message->params['value']['cancellable']);

        $notifier->end($token, 'end message');
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('$/progress', $message->method);
        self::assertEquals((string) $token, $message->params['token']);
        self::assertEquals('end', $message->params['value']['kind']);
        self::assertEquals('end message', $message->params['value']['message']);
    }

    public function testNotifyWithoutWorkDoneProgressCapability(): void
    {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifierWithoutProgressCapability();

        $notifier->create($token);
        $message = $this->transmitter->shiftNotification();
        self::assertNull($message); // Fake response so no message sent

        $notifier->begin($token, 'title', 'begin message');
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('window/showMessage', $message->method);
        self::assertEquals(MessageType::INFO, $message->params['type']);
        self::assertEquals('begin message', $message->params['message']);

        $notifier->report($token, 'report message', 30);
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('window/showMessage', $message->method);
        self::assertEquals(MessageType::INFO, $message->params['type']);
        self::assertEquals('report message - 30%', $message->params['message']);

        $notifier->end($token, 'end message');
        $message = $this->transmitter->shiftNotification();
        self::assertEquals('window/showMessage', $message->method);
        self::assertEquals(MessageType::INFO, $message->params['type']);
        self::assertEquals('end message', $message->params['message']);
    }

    private function createNotifierWithProgressCapability() : ClientCapabilityDependentProgressNotifier
    {
        return $this->createNotifier(true);
    }

    private function createNotifierWithoutProgressCapability() : ClientCapabilityDependentProgressNotifier
    {
        return $this->createNotifier(false);
    }

    private function createNotifier(bool $WorkDoneProgress) : ClientCapabilityDependentProgressNotifier
    {
        $api = new ClientApi($this->client);
        $capabilities = ClientCapabilities::fromArray([
            'window' => ['workDoneProgress' => $WorkDoneProgress],
        ]);

        return new ClientCapabilityDependentProgressNotifier($api, $capabilities);
    }
}
