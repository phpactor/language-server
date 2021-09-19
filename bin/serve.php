#!/usr/bin/env php
<?php

use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\Psr\AggregateEventDispatcher;
use Phpactor\LanguageServer\Core\CodeAction\AggregateCodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\AggregateDiagnosticsProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsEngine;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\DeferredResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\ServerStats;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Example\CodeAction\SayHelloCodeActionProvider;
use Phpactor\LanguageServer\Example\Command\SayHelloCommand;
use Phpactor\LanguageServer\Example\Diagnostics\SayHelloDiagnosticsProvider;
use Phpactor\LanguageServer\Handler\TextDocument\CodeActionHandler;
use Phpactor\LanguageServer\Handler\Workspace\DidChangeWatchedFilesHandler;
use Phpactor\LanguageServer\Listener\DidChangeWatchedFilesListener;
use Phpactor\LanguageServer\Listener\ServiceListener;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProviders;
use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\System\StatsHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Phpactor\LanguageServer\Core\Command\CommandDispatcher;
use Phpactor\LanguageServer\Middleware\ResponseHandlingMiddleware;
use Phpactor\LanguageServer\Service\DiagnosticsService;
use Psr\Log\AbstractLogger;
use function Safe\fopen;

require __DIR__ . '/../vendor/autoload.php';

$options = [
    'type' => 'tcp',
    'address' => null,
];

$options = array_merge($options, getopt('t::a::', ['type::', 'address::']));
$type = $options['type'];
$address = $options['address'];
if ($type === 'tcp' && !is_string($address)) {
    throw new RuntimeException('Address should be a string');
}

$in = fopen('php://stdin', 'r');
$out = fopen('php://stdout', 'w');

$logger = new class extends AbstractLogger {
    /** @var resource */
    private $err;
    /** @var resource */
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
$builder = LanguageServerBuilder::create(new ClosureDispatcherFactory(
    function (MessageTransmitter $transmitter, InitializeParams $params) use ($logger, $stats) {
        $responseWatcher = new DeferredResponseWatcher();
        $clientApi = new ClientApi(new JsonRpcClient($transmitter, $responseWatcher));

        $diagnosticsService = new DiagnosticsService(
            new DiagnosticsEngine($clientApi, new AggregateDiagnosticsProvider(
                $logger,
                new SayHelloDiagnosticsProvider()
            ))
        );

        $serviceProviders = new ServiceProviders($diagnosticsService);

        $workspace = new Workspace();
        $serviceManager = new ServiceManager($serviceProviders, $logger);
        $eventDispatcher = new AggregateEventDispatcher(
            new ServiceListener($serviceManager),
            new WorkspaceListener($workspace),
            new DidChangeWatchedFilesListener($clientApi, ['**/*.php'], $params->capabilities),
            $diagnosticsService
        );

        $handlers = new Handlers(
            new TextDocumentHandler($eventDispatcher),
            new StatsHandler($clientApi, $stats),
            new ServiceHandler($serviceManager, $clientApi),
            new CommandHandler(new CommandDispatcher([
                'phpactor.say_hello' => new SayHelloCommand($clientApi)
            ])),
            new DidChangeWatchedFilesHandler($eventDispatcher),
            new CodeActionHandler(new AggregateCodeActionProvider(
                new SayHelloCodeActionProvider()
            ), $workspace),
            new ExitHandler()
        );

        $runner = new HandlerMethodRunner(
            $handlers,
            new ChainArgumentResolver(
                new LanguageSeverProtocolParamsResolver(),
                new PassThroughArgumentResolver()
            ),
        );

        return new MiddlewareDispatcher(
            new ErrorHandlingMiddleware($logger),
            new InitializeMiddleware($handlers, $eventDispatcher, [
                'version' => 1,
            ]),
            new CancellationMiddleware($runner),
            new ResponseHandlingMiddleware($responseWatcher),
            new HandlerMiddleware($runner)
        );
    }
), $logger);

if ($type === 'tcp') {
    /** @phpstan-ignore-next-line */
    $builder->tcpServer((string)$address);
}
$builder
    ->withServerStats($stats)
    ->build()
    ->run();
