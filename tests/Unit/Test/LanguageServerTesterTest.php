<?php

namespace Phpactor\LanguageServer\Tests\Unit\Test;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phly\EventDispatcher\EventDispatcher;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Handler\ClosureHandler;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Service\ServiceListener;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\ServiceProvider\PingProvider;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
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
        $tester->textDocument()->open('file://foobar', 'content');
        $this->addToAssertionCount(1);
    }

    public function testServiceListRunning(): void
    {
        $tester = $this->createTester();
        $tester->initialize();
        $services = $tester->services()->listRunning();
        self::assertContains('ping', $services);

        $tester->services()->stop('ping');
        $services = $tester->services()->listRunning();
        self::assertNotContains('ping', $services);

        $tester->services()->start('ping');
        $services = $tester->services()->listRunning();
        self::assertContains('ping', $services);
    }

    public function testInitialize(): void
    {
        $tester = $this->createTester();
        $initializeResult = $tester->initialize();
        self::assertInstanceOf(ServerCapabilities::class, $initializeResult->capabilities);
    }

    private function createTester(?InitializeParams $params = null): LanguageServerTester
    {
        $params = $params ?: ProtocolFactory::initializeParams();

        return new LanguageServerTester(new ClosureDispatcherFactory(function (MessageTransmitter $transmitter, InitializeParams $params) {
            $responseWatcher = new DeferredResponseWatcher();
            $logger = new NullLogger();
            $clientApi = new ClientApi(new JsonRpcClient($transmitter, $responseWatcher));

            $serviceProviders = new ServiceProviders([
                new PingProvider($clientApi)
            ]);

            $serviceManager = new ServiceManager($serviceProviders, $logger);
            $eventDispatcher = new EventDispatcher(new ServiceListener($serviceManager));

            $handlers = new Handlers([
                new TextDocumentHandler(new NullEventDispatcher()),
                new ServiceHandler($serviceManager, $clientApi),
                new CommandHandler(new CommandDispatcher([])),
                new ClosureHandler('foobar', function ($args) use ($transmitter) {
                    $transmitter->transmit(new NotificationMessage(self::SUCCESS, [
                        'message' => self::SUCCESS
                    ]));

                    return new Success(self::SUCCESS);
                })
            ]);

            $runner = new HandlerMethodRunner(
                $handlers,
                new HandlerMethodResolver(),
                new ChainArgumentResolver(
                    new LanguageSeverProtocolParamsResolver(),
                    new PassThroughArgumentResolver()
                )
            );

            return new MiddlewareDispatcher(
                new InitializeMiddleware($handlers, $eventDispatcher),
                new CancellationMiddleware($runner),
                new HandlerMiddleware($runner)
            );
        }), $params);
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
