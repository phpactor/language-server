#!/usr/bin/env php
<?php

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\Handler\Initialize;
use Phpactor\LanguageServer\Example\ExampleCompletionHandler;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Phpactor\LanguageServer\Core\ChunkIO\TcpIO;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\Log\AbstractLogger;

require __DIR__ . '/../vendor/autoload.php';

$options = [
    'type' => 'tcp',
    'address' => '127.0.0.1:8888',
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
        ).PHP_EOL;
        fwrite($this->err, $message);
        fwrite($this->log, $message);
    }
};

$logger->info('test language server starting');
$logger->info('i am a demonstration server and provide no functionality');

$sessionManager = new Manager();
$builder = LanguageServerBuilder::create($logger, $sessionManager);

switch ($options['type']) {
    case 'tcp':
        $builder->tcpServer($options['address']);
        break;
    case 'stdio':
        $builder->stdIoServer();
        break;
    default:
        echo sprintf('Invalid builder type, must be either "tcp" or "stdio", got "%s"', $options['type']);
        exit(255);
}

$builder->recordTo('recording.log');
$builder->coreHandlers();
$builder->addHandler(new ExampleCompletionHandler($sessionManager));
$server = $builder->build();

$server->start();
