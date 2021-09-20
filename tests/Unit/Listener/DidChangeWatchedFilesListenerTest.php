<?php

namespace Phpactor\LanguageServer\Tests\Unit\Listener;

use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileSystemWatcher;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\LanguageServer\Listener\DidChangeWatchedFilesListener;
use Phpactor\TestUtils\PHPUnit\TestCase;

class DidChangeWatchedFilesListenerTest extends TestCase
{
    /**
     * @var DidChangeWatchedFilesListener
     */
    private $listener;

    /**
     * @var TestMessageTransmitter
     */
    private $transmitter;

    public function testDynamicallyRegisterIfSupported(): void
    {
        $this->initListener(new ClientCapabilities([
            'didChangeWatchedFiles' => ['dynamicRegistration' => true],
        ]));
        $this->dispatch(
            new Initialized(),
        );

        self::assertCount(1, $this->transmitter);

        $request = $this->transmitter->shiftRequest();
        self::assertEquals('client/registerCapability', $request->method);
        $registrations = $request->params['registrations'] ?? null;
        self::assertIsArray($registrations);
        self::assertCount(1, $registrations);

        $registration = $registrations[0];
        self::assertInstanceOf(Registration::class, $registration);
        self::assertEquals('workspace/didChangeWatchedFiles', $registration->method);

        $options = $registration->registerOptions;
        self::assertInstanceOf(DidChangeWatchedFilesRegistrationOptions::class, $options);
        self::assertCount(1, $options->watchers);

        $watcher = $options->watchers[0];
        self::assertInstanceOf(FileSystemWatcher::class, $watcher);
        self::assertEquals('*.php', $watcher->globPattern);
        self::assertNull($watcher->kind);
    }

    public function testDoesNotDynamicallyRegisterIfNotSupported(): void
    {
        $this->initListener(new ClientCapabilities());
        $this->dispatch(
            new Initialized(),
        );

        self::assertCount(0, $this->transmitter);
    }

    protected function initListener(ClientCapabilities $clientCapabilities): void
    {
        $client = TestRpcClient::create();
        $api = new ClientApi($client);
        $this->transmitter = $client->transmitter();
        $this->listener = new DidChangeWatchedFilesListener($api, ['*.php'], $clientCapabilities);
    }

    private function dispatch(object $event): void
    {
        foreach ($this->listener->getListenersForEvent($event) as $listener) {
            $listener($event);
        };
    }
}
