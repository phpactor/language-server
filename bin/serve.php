#!/usr/bin/env php
<?php

use Phly\EventDispatcher\EventDispatcher;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Service\ServiceListener;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\ServiceProvider\PingProvider;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\System\SystemHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Workspace\CommandDispatcher;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

$options = [
    'type' => 'tcp',
    'address' => null,
];

$options = array_merge($options, getopt('t::a::', ['type::', 'address::']));
$address = $options['address'];
if (!is_string($address)) {
    throw new RuntimeException('Address should be a string');
}

$in = fopen('php://stdin', 'r');
$out = fopen('php://stdout', 'w');

$logger = new class extends AbstractLogger {
    private $err;
    private $log;
    public function __construct()
    {
        $this->err = fopen('php://stderr', 'w');
        $this->log = fopen('phpactor-lsp.log', 'w');
    }

    public function log($level, $message, array $context = [])
    {
        $message = json_encode(
            [
                'level' => $level,
                'message' => $message,
                'context' => $context
            ]
        ).PHP_EOL.PHP_EOL;
        fwrite($this->err, $message);
        fwrite($this->log, $message);
    }
};

$logger->info('test language server starting');
$logger->info('i am a demonstration server and provide no functionality');

$stats = new ServerStats();
LanguageServerBuilder::create(new ClosureDispatcherFactory(
    function (MessageTransmitter $transmitter, InitializeParams $params) use ($logger, $stats) {
        $responseWatcher = new DeferredResponseWatcher();
        $clientApi = new ClientApi(new JsonRpcClient($transmitter, $responseWatcher));

        $serviceProviders = new ServiceProviders([
            new PingProvider($clientApi)
        ]);

        $serviceManager = new ServiceManager($serviceProviders, $logger);
        $eventDispatcher = new EventDispatcher(new ServiceListener($serviceManager));

        $handlers = new Handlers([
            new TextDocumentHandler(new NullEventDispatcher()),
            new SystemHandler($clientApi, $stats),
            new ServiceHandler($serviceManager, $clientApi),
            new CommandHandler(new CommandDispatcher([])),
            new ExitHandler(),
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
            new ErrorHandlingMiddleware($logger),
            new InitializeMiddleware($handlers, $eventDispatcher),
            new CancellationMiddleware($runner),
            new HandlerMiddleware($runner)
        );
    }
), $logger)
    ->withServerStats($stats)
    ->tcpServer($address)
    ->build()
    ->run();
