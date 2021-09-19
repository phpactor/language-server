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

    public function testBegin(): void
    {
        $token = WorkDoneToken::generate();
        $this->createNotifier()->begin($token, 'Hello');
        self::assertEquals(1, $this->transmitter->count());
        self::assertEquals('Hello', $this->transmitter->shiftNotification()->params['message']);
    }

    public function testBeginMessageAndPercentage(): void
    {
        $token = WorkDoneToken::generate();
        $this->createNotifier()->begin($token, 'Indexer', 'this may take some time', 50);
        self::assertEquals(1, $this->transmitter->count());
        self::assertEquals('Indexer: this may take some time, 50% done', $this->transmitter->shiftNotification()->params['message']);
    }

    private function createNotifier(): ProgressNotifier
    {
        return new MessageProgressNotifier(new ClientApi($this->api));
    }
}
