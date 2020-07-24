<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;

class LanguageServerTesterTest extends TestCase
{
    const SUCCESS = 'success';

    public function testDispatch(): void
    {
        $tester = $this->createTester();
        $response = wait($tester->dispatch(
            ProtocolFactory::requestMessage(
                'foobar',
                ['foo' => 'Barfoo']
            )
        ));
        $this->assertSuccessResponse($response);
    }

    public function testDispatchAndWait(): void
    {
        $tester = $this->createTester();
        $response = $tester->dispatchAndWait(
            ProtocolFactory::requestMessage(
                'foobar',
                ['foo' => 'Barfoo']
            )
        );
        $this->assertSuccessResponse($response);
    }

    public function testRequest(): void
    {
        $tester = $this->createTester();
        $response = wait($tester->request('foobar', ['foobar' => 'Barfoo']));
        $this->assertSuccessResponse($response);
    }

    public function testRequestAndWait(): void
    {
        $tester = $this->createTester();
        $response = $tester->requestAndWait('foobar', ['foobar' => 'Barfoo']);
        $this->assertSuccessResponse($response);
    }

    public function testNotify(): void
    {
        $tester = $this->createTester();
        $response = wait($tester->notify('foobar', ['foobar' => 'Barfoo']));
        $this->assertNotifysTransmission($tester);
    }

    public function testNotifyAndWait(): void
    {
        $tester = $this->createTester();
        $tester->notifyAndWait('foobar', ['foobar' => 'Barfoo']);
        $this->assertNotifysTransmission($tester);
    }

    public function testOpenTextDocument(): void
    {
        $tester = $this->createTester();
        $tester->openTextDocument('file://foobar', 'content');
        $this->assertNotifysTransmission($tester);
    }

    private function createTester(?ClientCapabilities $capabilties = null): LanguageServerTester
    {
        $capabilties = $capabilties ?: new ClientCapabilities();

        return new LanguageServerTester(new ClosureDispatcherFactory(function (MessageTransmitter $transmitter, InitializeParams $params) {
            return new ClosureDispatcher(function (Message $message) use ($transmitter) {
                if ($message instanceof RequestMessage) {
                    return new Success(new ResponseMessage($message->id, self::SUCCESS));
                }

                $transmitter->transmit(new NotificationMessage(self::SUCCESS, [
                    'message' => self::SUCCESS
                ]));

                return new Success(null);
            });
        }), $capabilties);
    }

    private function assertSuccessResponse(ResponseMessage $response): void
    {
        self::assertEquals(self::SUCCESS, $response->result);
    }

    private function assertNotifysTransmission(LanguageServerTester $tester): void
    {
        $notification = $tester->transmitter()->shift();
        self::assertNotNull($notification);
        self::assertInstanceOf(NotificationMessage::class, $notification);
        assert($notification instanceof NotificationMessage);
        self::assertEquals(self::SUCCESS, $notification->params['message']);
    }
}
