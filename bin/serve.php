#!/usr/bin/env php
<?php

use Amp\Loop;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Adapter\Psr\NullEventDispatcher;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\ChainArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\LanguageSeverProtocolParamsResolver;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver\PassThroughArgumentResolver;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ClosureDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MiddlewareDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Factory\ClosureDispatcherFactory;
use Phpactor\LanguageServer\Core\Handler\AggregateHandlerLoader;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodResolver;
use Phpactor\LanguageServer\Core\Handler\HandlerMethodRunner;
use Phpactor\LanguageServer\Core\Handler\Handlers;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\LanguageServer;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\JsonRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Phpactor\LanguageServer\Core\Session\Workspace;
use Phpactor\LanguageServer\Extension\Core\Initialize;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Phpactor\LanguageServer\Core\ChunkIO\TcpIO;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Session\SessionManager;
use Phpactor\LanguageServer\Handler\Example\PingHandler;
use Phpactor\LanguageServer\Handler\Example\ProgressHandler;
use Phpactor\LanguageServer\Handler\System\ExitHandler;
use Phpactor\LanguageServer\Handler\System\ServiceHandler;
use Phpactor\LanguageServer\Handler\TextDocument\TextDocumentHandler;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Middleware\CancellationMiddleware;
use Phpactor\LanguageServer\Middleware\ErrorHandlingMiddleware;
use Phpactor\LanguageServer\Middleware\HandlerMiddleware;
use Phpactor\LanguageServer\Middleware\InitializeMiddleware;
use Psr\Log\AbstractLogger;
use function Amp\call;

require __DIR__ . '/../vendor/autoload.php';

$options = [
    'type' => 'tcp',
    'address' => null,
];

$options = array_merge($options, getopt('t::a::', ['type::', 'address::']));

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

LanguageServerBuilder::create(new ClosureDispatcherFactory(
    function (MessageTransmitter $transmitter, InitializeParams $params) use ($logger) {

        $handlers = new Handlers([
            new TextDocumentHandler(new NullEventDispatcher()),
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
            new InitializeMiddleware($handlers),
            new CancellationMiddleware($runner),
            new HandlerMiddleware($runner)
        );
    }
), $logger)
    ->tcpServer($options['address'])
    ->build()
    ->run();
