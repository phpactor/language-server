<?php

namespace Phpactor\LanguageServer\Tests\Unit\Core\Server;

use Amp\PHPUnit\AsyncTestCase;
use Closure;
use Generator;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResponse;
use Phpactor\LanguageServerProtocol\DidChangeWatchedFilesRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileSystemWatcher;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\LanguageServerProtocol\Registration;
use Phpactor\LanguageServerProtocol\Unregistration;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;

class ClientApiTest extends AsyncTestCase
{
    /**
     * @dataProvider provideWindowShowMessage
     * @dataProvider provideWindowLogMessage
     * @dataProvider provideWindowShowMessageRequest
     * @dataProvider provideWorkspaceEdit
     * @dataProvider provideWorkspaceExecuteCommand
     * @dataProvider provideDiagnostics
     * @dataProvider provideRegisterCapability
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
            function (ClientApi $api): void {
                $api->window()->showMessage()->error('foobar');
                $api->window()->showMessage()->log('foobar');
                $api->window()->showMessage()->info('foobar');
                $api->window()->showMessage()->warning('foobar');
            },
            function (TestRpcClient $client): void {
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
            function (ClientApi $api): void {
                $api->window()->logMessage()->error('foobar');
                $api->window()->logMessage()->log('foobar');
                $api->window()->logMessage()->info('foobar');
                $api->window()->logMessage()->warning('foobar');
            },
            function (TestRpcClient $client): void {
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
                return $api->window()->showMessageRequest()->info('foobar', new MessageActionItem('foobar'));
            },
            function (TestRpcClient $client, $result): void {
                $client->responseWatcher()->resolveLastResponse(['title' => 'foobar']);
                $message = $client->transmitter()->shiftRequest();
                self::assertEquals('window/showMessageRequest', $message->method);
                self::assertEquals(MessageType::INFO, $message->params['type']);
                self::assertEquals('foobar', $message->params['message']);

                $result = \Amp\Promise\wait($result);
                self::assertInstanceOf(MessageActionItem::class, $result);
                self::assertEquals('foobar', $result->title);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideWorkspaceEdit(): Generator
    {
        yield [
            function (ClientApi $api) {
                return $api->workspace()->applyEdit(new WorkspaceEdit([]));
            },
            function (TestRpcClient $client, $result): void {
                $client->responseWatcher()->resolveLastResponse([
                    'applied' => false,
                    'failureReason' => 'sorry',
                ]);
                $message = $client->transmitter()->shiftRequest();
                self::assertEquals('workspace/applyEdit', $message->method);

                $result = \Amp\Promise\wait($result);
                self::assertInstanceOf(ApplyWorkspaceEditResponse::class, $result);
                self::assertFalse($result->applied);
                self::assertEquals('sorry', $result->failureReason);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideWorkspaceExecuteCommand(): Generator
    {
        yield [
            function (ClientApi $api) {
                return $api->workspace()->executeCommand('one', ['one', 'two']);
            },
            function (TestRpcClient $client, $result): void {
                $client->responseWatcher()->resolveLastResponse('result');
                $message = $client->transmitter()->shiftRequest();
                self::assertEquals('workspace/executeCommand', $message->method);

                $result = \Amp\Promise\wait($result);
                self::assertEquals('result', $result);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideDiagnostics(): Generator
    {
        yield [
            function (ClientApi $api): void {
                $api->diagnostics()->publishDiagnostics(
                    'file://file.php',
                    1,
                    [
                    ]
                );
            },
            function (TestRpcClient $client, $result): void {
                $message = $client->transmitter()->shiftNotification();
                self::assertEquals('textDocument/publishDiagnostics', $message->method);
            }
        ];
    }

    /**
     * @reuturn Generator<mixed>
     */
    public function provideRegisterCapability(): Generator
    {
        yield [
            function (ClientApi $api): void {
                $api->client()->registerCapability(
                    new Registration('foobar', 'workspace/didChangeWatchedFiles', new DidChangeWatchedFilesRegistrationOptions([
                        new FileSystemWatcher('**/*.php')
                    ]))
                );
            },
            function (TestRpcClient $client, $result): void {
                $message = $client->transmitter()->shiftNotification();
                self::assertEquals('client/registerCapability', $message->method);
            }
        ];

        yield [
            function (ClientApi $api): void {
                $api->client()->unregisterCapability(
                    new Unregistration('10', 'foo')
                );
            },
            function (TestRpcClient $client, $result): void {
                $message = $client->transmitter()->shiftNotification();
                self::assertEquals('client/unregisterCapability', $message->method);
            }
        ];
    }
}
