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
        $this->assertEquals('window/workDoneProgress/create', $message->method);
        $this->assertEquals((string) $token, $message->params['token']);

        $notifier->begin($token, 'title', 'begin message');
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('$/progress', $message->method);
        $this->assertEquals((string) $token, $message->params['token']);
        $this->assertEquals('begin', $message->params['value']['kind']);
        $this->assertEquals('title', $message->params['value']['title']);
        $this->assertEquals('begin message', $message->params['value']['message']);
        $this->assertNull($message->params['value']['percentage']);
        $this->assertNull($message->params['value']['cancellable']);

        $notifier->report($token, 'report message', 30);
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('$/progress', $message->method);
        $this->assertEquals((string) $token, $message->params['token']);
        $this->assertEquals('report', $message->params['value']['kind']);
        $this->assertEquals('report message', $message->params['value']['message']);
        $this->assertEquals(30, $message->params['value']['percentage']);
        $this->assertNull($message->params['value']['cancellable']);

        $notifier->end($token, 'end message');
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('$/progress', $message->method);
        $this->assertEquals((string) $token, $message->params['token']);
        $this->assertEquals('end', $message->params['value']['kind']);
        $this->assertEquals('end message', $message->params['value']['message']);
    }

    public function testNotifyWithoutWorkDoneProgressCapability(): void
    {
        $token = WorkDoneToken::generate();
        $notifier = $this->createNotifierWithoutProgressCapability();

        $notifier->create($token);
        $message = $this->transmitter->shiftNotification();
        $this->assertNull($message); // Fake response so no message sent

        $notifier->begin($token, 'title', 'begin message');
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('window/showMessage', $message->method);
        $this->assertEquals(MessageType::INFO, $message->params['type']);
        $this->assertEquals('begin message', $message->params['message']);

        $notifier->report($token, 'report message', 30);
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('window/showMessage', $message->method);
        $this->assertEquals(MessageType::INFO, $message->params['type']);
        $this->assertEquals('report message - 30%', $message->params['message']);

        $notifier->end($token, 'end message');
        $message = $this->transmitter->shiftNotification();
        $this->assertEquals('window/showMessage', $message->method);
        $this->assertEquals(MessageType::INFO, $message->params['type']);
        $this->assertEquals('end message', $message->params['message']);
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
