<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server;

use Amp\PHPUnit\AsyncTestCase;
use Closure;
use Generator;
use LanguageServerProtocol\MessageType;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;

class ClientApiTest extends AsyncTestCase
{
    /**
     * @dataProvider provideWindowShowMessage
     * @dataProvider provideWindowLogMessage
     * @dataProvider provideWindowShowMessageRequest
     */
    public function testSend(Closure $executor, Closure $assertions): void
    {
        $client = TestRpcClient::create();
        $api = new ClientApi($client);
        $result = $executor($api);

        $assertions($client, $result);
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideWindowShowMessage(): Generator
    {
        yield [
            function (ClientApi $api) {
                $api->window()->showMessage()->error('foobar');
                $api->window()->showMessage()->log('foobar');
                $api->window()->showMessage()->info('foobar');
                $api->window()->showMessage()->warning('foobar');
            },
            function (TestRpcClient $client) {
                $message = $client->transmitter()->shiftNotification();
                self::assertEquals('window/showMessage', $message->method);
                self::assertEquals(MessageType::ERROR, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::LOG, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::INFO, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::WARNING, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideWindowLogMessage(): Generator
    {
        yield [
            function (ClientApi $api) {
                $api->window()->logMessage()->error('foobar');
                $api->window()->logMessage()->log('foobar');
                $api->window()->logMessage()->info('foobar');
                $api->window()->logMessage()->warning('foobar');
            },
            function (TestRpcClient $client) {
                $message = $client->transmitter()->shiftNotification();
                self::assertEquals('window/logMessage', $message->method);
                self::assertEquals(MessageType::ERROR, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::LOG, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::INFO, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $message = $client->transmitter()->shiftNotification();
                self::assertEquals(MessageType::WARNING, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideWindowShowMessageRequest(): Generator
    {
        yield [
            function (ClientApi $api) {
                return $api->window()->showMessageRequest()->info('foobar');
            },
            function (TestRpcClient $client, $result) {
                $message = $client->transmitter()->shiftRequest();
                self::assertEquals('window/showMessageRequest', $message->method);
                self::assertEquals(MessageType::INFO, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);
            }
        ];
    }
}
